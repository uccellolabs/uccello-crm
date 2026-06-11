<?php

namespace App\Infrastructure\Assistant;

use App\Application\Crm\Services\CustomFields;
use App\Application\Crm\Services\Picklists;
use App\Domain\Shared\Enums\CustomFieldType;
use App\Domain\Shared\Enums\DealStatus;
use App\Domain\Shared\Enums\Picklist;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\CustomFieldDefinition;
use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Throwable;

/**
 * The CRM-data tool surface exposed to the assistant. It defines the tools the
 * model may call, executes them against the (team-scoped) Eloquent models, and
 * describes every module — including team-defined custom fields — so the model
 * knows what it can search, read, and aggregate.
 *
 * Every query runs through the models' `BelongsToTeam` global scope, so the
 * assistant can never read another team's records.
 */
class AssistantTools
{
    /** @var list<string> */
    private const MODULES = ['company', 'contact', 'deal', 'task', 'activity'];

    private const SEARCH_LIMIT = 25;

    private const AGGREGATE_SCAN_CAP = 5000;

    /**
     * Per-request cache of relation-label lookups for custom-field relation values.
     *
     * @var array<string, string|null>
     */
    private array $relationLabelCache = [];

    /**
     * Tool activity recorded during a run, surfaced to the UI as chips.
     *
     * @var list<array{name: string, summary: string}>
     */
    private array $activity = [];

    public function __construct(private readonly CustomFields $customFields) {}

    /**
     * The CRM modules the assistant may query (used for tool-input enums).
     *
     * @return list<string>
     */
    public function modules(): array
    {
        return self::MODULES;
    }

    /**
     * Execute a tool call and return a model-facing payload plus a short,
     * human-readable summary. The summary is recorded as activity so the agent
     * loop can surface it as a UI chip after the run.
     *
     * @param  array<string, mixed>  $input
     * @return array{data: mixed, summary: string, error: bool}
     */
    public function execute(string $name, array $input): array
    {
        try {
            $result = match ($name) {
                'search_records' => $this->searchRecords($input),
                'get_record' => $this->getRecord($input),
                'aggregate_records' => $this->aggregateRecords($input),
                default => $this->error("Outil inconnu : {$name}."),
            };
        } catch (Throwable $e) {
            $result = $this->error('Erreur lors de l\'exécution : '.$e->getMessage());
        }

        $this->activity[] = ['name' => $name, 'summary' => $result['summary']];

        return $result;
    }

    /**
     * Drain and return the tool activity recorded so far (for the UI trace).
     *
     * @return list<array{name: string, summary: string}>
     */
    public function takeActivity(): array
    {
        $activity = $this->activity;
        $this->activity = [];

        return $activity;
    }

    /**
     * A compact description of every module and its custom fields for the system
     * prompt, so the model knows what it can query and which values are valid.
     */
    public function schema(): string
    {
        $lines = ['## Modules du CRM et leurs champs', ''];

        foreach (self::MODULES as $module) {
            $lines[] = "### {$this->moduleLabel($module)} (module `{$module}`)";
            $lines[] = '- Champs standards : '.implode(', ', $this->standardFieldList($module));

            $custom = $this->customFieldSummary($module);
            $lines[] = $custom === ''
                ? '- Champs personnalisés : aucun'
                : '- Champs personnalisés : '.$custom;

            $lines[] = '';
        }

        $stages = PipelineStage::query()->orderBy('position')->pluck('name')->all();
        if ($stages !== []) {
            $lines[] = 'Étapes du pipeline : '.implode(', ', $stages).'.';
        }
        $lines[] = 'Statuts d\'opportunité : '.collect(DealStatus::cases())
            ->map(fn (DealStatus $s) => "{$s->value} ({$s->label()})")->implode(', ').'.';
        $lines[] = 'Secteurs (industry) : '.$this->picklistValues(Picklist::Industry).'.';
        $lines[] = 'Priorités de tâche : '.$this->picklistValues(Picklist::TaskPriority).'.';
        $lines[] = 'Types d\'activité : '.$this->picklistValues(Picklist::ActivityType).'.';

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{data: mixed, summary: string, error: bool}
     */
    private function searchRecords(array $input): array
    {
        $module = $this->validModule($input['module'] ?? null);
        if ($module === null) {
            return $this->error('Module invalide.');
        }

        $query = $this->baseQuery($module);
        $this->applyTextSearch($query, $module, $this->stringOrNull($input['query'] ?? null));
        $ignored = $this->applyFilters($query, $module, $this->arrayOrEmpty($input['filters'] ?? null));
        $this->applySort($query, $module, $input);

        $limit = max(1, min(self::SEARCH_LIMIT, (int) ($input['limit'] ?? self::SEARCH_LIMIT)));

        $records = $query->limit($limit)->get()
            ->map(fn (Model $model) => $this->serialize($module, $model))
            ->all();

        $data = ['count' => count($records), 'records' => $records];
        if ($ignored !== []) {
            $data['ignored_filters'] = $ignored;
        }

        return [
            'data' => $data,
            'summary' => "Recherche · {$this->moduleLabel($module)} (".count($records).' résultat'.(count($records) > 1 ? 's' : '').')',
            'error' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{data: mixed, summary: string, error: bool}
     */
    private function getRecord(array $input): array
    {
        $module = $this->validModule($input['module'] ?? null);
        if ($module === null) {
            return $this->error('Module invalide.');
        }

        $id = (int) ($input['id'] ?? 0);
        $model = $this->baseQuery($module)->find($id);

        if ($model === null) {
            return $this->error("Aucun enregistrement #{$id} dans {$this->moduleLabel($module)}.");
        }

        $record = $this->serialize($module, $model);
        $record['related'] = $this->relatedFor($module, $model);

        return [
            'data' => ['record' => $record],
            'summary' => "Fiche · {$this->moduleLabel($module)} #{$id}",
            'error' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{data: mixed, summary: string, error: bool}
     */
    private function aggregateRecords(array $input): array
    {
        $module = $this->validModule($input['module'] ?? null);
        if ($module === null) {
            return $this->error('Module invalide.');
        }

        $operation = in_array($input['operation'] ?? '', ['count', 'sum', 'avg'], true)
            ? (string) $input['operation']
            : 'count';
        $field = $this->stringOrNull($input['field'] ?? null);
        $groupBy = $this->stringOrNull($input['group_by'] ?? null);

        $query = $this->baseQuery($module);
        $this->applyFilters($query, $module, $this->arrayOrEmpty($input['filters'] ?? null));

        if ($groupBy === null) {
            $value = $this->computeMetric($query, $operation, $field);

            return [
                'data' => ['operation' => $operation, 'field' => $field, 'value' => $value],
                'summary' => "Agrégat · {$this->moduleLabel($module)} ({$operation})",
                'error' => false,
            ];
        }

        $groups = [];
        $query->limit(self::AGGREGATE_SCAN_CAP)->get()->each(function (Model $model) use (&$groups, $module, $groupBy, $field) {
            $key = $this->groupValue($module, $model, $groupBy) ?? '—';
            $groups[$key] ??= ['count' => 0, 'sum' => 0.0];
            $groups[$key]['count']++;
            if ($field !== null) {
                $groups[$key]['sum'] += (float) ($model->getAttribute($field) ?? 0);
            }
        });

        $rows = [];
        foreach ($groups as $key => $agg) {
            $rows[] = [
                'group' => $key,
                'value' => match ($operation) {
                    'sum' => round($agg['sum'], 2),
                    'avg' => round($agg['sum'] / $agg['count'], 2),
                    default => $agg['count'],
                },
            ];
        }

        return [
            'data' => ['operation' => $operation, 'group_by' => $groupBy, 'groups' => $rows],
            'summary' => "Agrégat · {$this->moduleLabel($module)} par {$groupBy}",
            'error' => false,
        ];
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function computeMetric(Builder $query, string $operation, ?string $field): float|int
    {
        return match ($operation) {
            'sum' => $field !== null ? round((float) $query->sum($field), 2) : 0,
            'avg' => $field !== null ? round((float) $query->avg($field), 2) : 0,
            default => $query->count(),
        };
    }

    /**
     * @return Builder<Model>
     */
    private function baseQuery(string $module): Builder
    {
        /** @var Builder<Model> $query */
        $query = $this->modelClass($module)::query()->with($this->eagerLoads($module));

        return $query;
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function applyTextSearch(Builder $query, string $module, ?string $term): void
    {
        if ($term === null) {
            return;
        }

        $columns = $this->textColumns($module);
        $query->where(function (Builder $sub) use ($columns, $term) {
            foreach ($columns as $column) {
                $sub->orWhere($column, 'ilike', "%{$term}%");
            }
        });
    }

    /**
     * Apply field filters, returning the keys that could not be mapped.
     *
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $filters
     * @return list<string>
     */
    private function applyFilters(Builder $query, string $module, array $filters): array
    {
        $standard = $this->standardFilters($module);
        $customDefs = $this->customDefinitions($module)->keyBy('key');
        $ignored = [];

        foreach ($filters as $field => $value) {
            $field = (string) $field;

            if (in_array($field, ['completed', 'is_completed'], true) && $module === 'task') {
                $isDone = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                $isDone ? $query->whereNotNull('completed_at') : $query->whereNull('completed_at');

                continue;
            }

            if (isset($standard[$field])) {
                $this->applyStandardFilter($query, $standard[$field], $value);

                continue;
            }

            /** @var CustomFieldDefinition|null $def */
            $def = $customDefs->get($field);
            if ($def !== null) {
                $this->applyCustomFilter($query, $def, $value);

                continue;
            }

            $ignored[] = $field;
        }

        return $ignored;
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array{type: string, target?: string}  $spec
     */
    private function applyStandardFilter(Builder $query, array $spec, mixed $value): void
    {
        $value = is_scalar($value) ? (string) $value : '';
        if ($value === '') {
            return;
        }

        match ($spec['type']) {
            'string' => $query->where($spec['target'] ?? '', 'ilike', "%{$value}%"),
            'exact' => $query->where($spec['target'] ?? '', $value),
            'relation' => $query->whereHas(
                $this->relationName($spec['target'] ?? ''),
                fn (Builder $sub) => $sub->where($this->relationColumn($spec['target'] ?? ''), 'ilike', "%{$value}%"),
            ),
            default => null,
        };
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function applyCustomFilter(Builder $query, CustomFieldDefinition $def, mixed $value): void
    {
        $column = "custom_fields->{$def->key}";

        if ($def->type === CustomFieldType::MultiSelect) {
            $query->whereJsonContains($column, $value);

            return;
        }

        if ($def->type === CustomFieldType::Checkbox) {
            $query->where($column, filter_var($value, FILTER_VALIDATE_BOOLEAN));

            return;
        }

        $query->where($column, is_scalar($value) ? $value : '');
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $input
     */
    private function applySort(Builder $query, string $module, array $input): void
    {
        $sort = $this->stringOrNull($input['sort'] ?? null);
        $direction = ($input['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sort !== null && in_array($sort, $this->sortableColumns($module), true)) {
            $query->orderBy($sort, $direction);

            return;
        }

        $query->orderByDesc('id');
    }

    /**
     * Serialize a record's standard fields + resolved custom fields.
     *
     * @return array<string, mixed>
     */
    private function serialize(string $module, Model $model): array
    {
        $base = match ($module) {
            'company' => $this->serializeCompany($model),
            'contact' => $this->serializeContact($model),
            'deal' => $this->serializeDeal($model),
            'task' => $this->serializeTask($model),
            'activity' => $this->serializeActivity($model),
            default => ['id' => $model->getKey()],
        };

        $entity = $this->entityType($module);
        if ($entity !== null) {
            /** @var array<string, mixed>|null $raw */
            $raw = $model->getAttribute('custom_fields');
            $custom = $this->resolveCustomFields($entity, $raw);
            if ($custom !== []) {
                $base['custom_fields'] = $custom;
            }
        }

        return $base;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeCompany(Model $m): array
    {
        /** @var Company $m */
        return [
            'id' => $m->id,
            'name' => $m->name,
            'domain' => $m->domain,
            'industry' => $m->industry,
            'phone' => $m->phone,
            'website' => $m->website,
            'city' => $m->city,
            'country' => $m->country,
            'owner' => $m->owner?->name,
            'created_at' => $m->created_at?->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeContact(Model $m): array
    {
        /** @var Contact $m */
        return [
            'id' => $m->id,
            'full_name' => $m->full_name,
            'email' => $m->email,
            'phone' => $m->phone,
            'job_title' => $m->job_title,
            'company' => $m->company?->name,
            'owner' => $m->owner?->name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeDeal(Model $m): array
    {
        /** @var Deal $m */
        return [
            'id' => $m->id,
            'name' => $m->name,
            'amount' => $m->amount !== null ? (float) $m->amount : null,
            'currency' => $m->currency,
            'status' => $m->status->value,
            'status_label' => $m->status->label(),
            'stage' => $m->stage->name,
            'pipeline' => $m->pipeline->name,
            'company' => $m->company?->name,
            'contact' => $m->contact?->full_name,
            'owner' => $m->owner?->name,
            'expected_close_date' => $m->expected_close_date?->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeTask(Model $m): array
    {
        /** @var Task $m */
        return [
            'id' => $m->id,
            'title' => $m->title,
            'description' => $m->description,
            'priority' => $m->priority,
            'priority_label' => app(Picklists::class)->label(Picklist::TaskPriority, $m->priority),
            'due_at' => $m->due_at?->toDateTimeString(),
            'is_completed' => $m->isCompleted(),
            'assignee' => $m->assignee?->name,
            'related_to' => $this->morphLabel($m->taskable_type, $m->taskable),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeActivity(Model $m): array
    {
        /** @var Activity $m */
        return [
            'id' => $m->id,
            'type' => $m->type,
            'type_label' => app(Picklists::class)->label(Picklist::ActivityType, $m->type),
            'subject' => $m->subject,
            'body' => $m->body,
            'occurred_at' => $m->occurred_at->toDateTimeString(),
            'user' => $m->user?->name,
            'related_to' => $this->morphLabel($m->subjectable_type, $m->subjectable),
        ];
    }

    /**
     * Related records, activities and tasks for the record-detail tool.
     *
     * @return array<string, mixed>
     */
    private function relatedFor(string $module, Model $model): array
    {
        $related = [];

        if ($module === 'company') {
            /** @var Company $model */
            $related['contacts'] = $model->contacts()->get()
                ->map(fn (Contact $c) => ['id' => $c->id, 'name' => $c->full_name, 'email' => $c->email])->all();
            $related['deals'] = $model->deals()->get()
                ->map(fn (Deal $d) => ['id' => $d->id, 'name' => $d->name, 'amount' => $d->amount !== null ? (float) $d->amount : null, 'status' => $d->status->value])->all();
        }

        if ($module === 'contact') {
            /** @var Contact $model */
            $related['deals'] = $model->deals()->get()
                ->map(fn (Deal $d) => ['id' => $d->id, 'name' => $d->name, 'amount' => $d->amount !== null ? (float) $d->amount : null, 'status' => $d->status->value])->all();
        }

        if (in_array($module, ['company', 'contact', 'deal'], true)) {
            /** @var Company|Contact|Deal $model */
            $related['activities'] = $model->activities()->latest('occurred_at')->limit(15)->get()
                ->map(fn (Activity $a) => ['type' => $a->type, 'subject' => $a->subject, 'occurred_at' => $a->occurred_at->toDateString()])->all();
            $related['tasks'] = $model->tasks()->get()
                ->map(fn (Task $t) => ['title' => $t->title, 'due_at' => $t->due_at?->toDateString(), 'is_completed' => $t->isCompleted()])->all();
        }

        return $related;
    }

    /**
     * Resolve a record's raw custom-field values to display values keyed by field key.
     *
     * @param  array<string, mixed>|null  $raw
     * @return array<string, mixed>
     */
    private function resolveCustomFields(string $entity, ?array $raw): array
    {
        if ($raw === null || $raw === []) {
            return [];
        }

        $defs = $this->customFields->definitions($entity)->keyBy('key');
        $out = [];

        foreach ($raw as $key => $value) {
            /** @var CustomFieldDefinition|null $def */
            $def = $defs->get($key);
            if ($def === null || $value === null || $value === '') {
                continue;
            }

            $out[$key] = [
                'label' => $def->label,
                'value' => $this->displayCustomValue($def, $value),
            ];
        }

        return $out;
    }

    private function displayCustomValue(CustomFieldDefinition $def, mixed $value): mixed
    {
        return match ($def->type) {
            CustomFieldType::Select => $this->choiceLabel($def, (string) $value),
            CustomFieldType::MultiSelect => collect(is_array($value) ? $value : [$value])
                ->map(fn ($v) => $this->choiceLabel($def, (string) $v))->all(),
            CustomFieldType::Checkbox => $value ? 'Oui' : 'Non',
            CustomFieldType::Relation => $this->relationLabel(
                (string) ($def->options['related_module'] ?? ''),
                (int) $value,
            ) ?? "#{$value}",
            default => $value,
        };
    }

    private function choiceLabel(CustomFieldDefinition $def, string $value): string
    {
        foreach ($def->choices() as $choice) {
            if ((string) $choice['value'] === $value) {
                return $choice['label'];
            }
        }

        return $value;
    }

    private function relationLabel(string $module, int $id): ?string
    {
        $cacheKey = "{$module}:{$id}";
        if (array_key_exists($cacheKey, $this->relationLabelCache)) {
            return $this->relationLabelCache[$cacheKey];
        }

        $label = match ($module) {
            'company' => Company::query()->whereKey($id)->value('name'),
            'deal' => Deal::query()->whereKey($id)->value('name'),
            'contact' => ($c = Contact::query()->find($id)) !== null ? $c->full_name : null,
            default => null,
        };

        return $this->relationLabelCache[$cacheKey] = $label !== null ? (string) $label : null;
    }

    private function morphLabel(?string $type, ?Model $model): ?string
    {
        if ($type === null || $model === null) {
            return null;
        }

        $name = $model instanceof Contact ? $model->full_name : $model->getAttribute('name');

        return is_string($name) ? "{$this->moduleLabel($type)} · {$name}" : null;
    }

    /**
     * Display name of a (possibly null) related record reached via a relation
     * attribute. Reads through `getAttribute` so the value is genuinely nullable
     * — Eloquent's relation typing would otherwise hide the null branch.
     */
    private function relatedDisplayName(Model $model, string $relation): ?string
    {
        $related = $model->getAttribute($relation);

        if ($related instanceof Contact) {
            return $related->full_name;
        }

        if ($related instanceof Model) {
            $name = $related->getAttribute('name');

            return is_string($name) ? $name : null;
        }

        return null;
    }

    private function groupValue(string $module, Model $model, string $groupBy): ?string
    {
        $standard = match ($groupBy) {
            'status' => $model instanceof Deal ? $model->status->label() : null,
            'stage' => $model instanceof Deal ? $model->stage->name : null,
            'pipeline' => $model instanceof Deal ? $model->pipeline->name : null,
            'currency' => $model instanceof Deal ? $model->currency : null,
            'industry' => $model instanceof Company ? ($model->industry ?? '—') : null,
            'priority' => $model instanceof Task ? app(Picklists::class)->label(Picklist::TaskPriority, $model->priority) : null,
            'type' => $model instanceof Activity ? app(Picklists::class)->label(Picklist::ActivityType, $model->type) : null,
            'owner' => ($model instanceof Company || $model instanceof Contact || $model instanceof Deal)
                ? ($this->relatedDisplayName($model, 'owner') ?? 'Sans responsable') : null,
            'assignee' => $model instanceof Task ? ($this->relatedDisplayName($model, 'assignee') ?? 'Non assignée') : null,
            'company' => ($model instanceof Contact || $model instanceof Deal)
                ? ($this->relatedDisplayName($model, 'company') ?? '—') : null,
            default => null,
        };

        if ($standard !== null) {
            return $standard;
        }

        $entity = $this->entityType($module);
        if ($entity !== null) {
            $def = $this->customFields->definitions($entity)->firstWhere('key', $groupBy);
            if ($def instanceof CustomFieldDefinition) {
                /** @var array<string, mixed>|null $raw */
                $raw = $model->getAttribute('custom_fields');
                $value = $raw[$groupBy] ?? null;
                if ($value === null || $value === '') {
                    return '—';
                }
                $display = $this->displayCustomValue($def, $value);

                return is_array($display) ? implode(', ', array_map('strval', $display)) : (string) $display;
            }
        }

        return null;
    }

    /* ----------------------------------------------------------------------
     | Module metadata
     * -------------------------------------------------------------------- */

    /**
     * @return class-string<Model>
     */
    private function modelClass(string $module): string
    {
        return match ($module) {
            'company' => Company::class,
            'contact' => Contact::class,
            'deal' => Deal::class,
            'task' => Task::class,
            'activity' => Activity::class,
            default => Company::class,
        };
    }

    private function entityType(string $module): ?string
    {
        return in_array($module, ['company', 'contact', 'deal', 'task'], true) ? $module : null;
    }

    private function moduleLabel(string $module): string
    {
        return match ($module) {
            'company' => 'Entreprises',
            'contact' => 'Contacts',
            'deal' => 'Opportunités',
            'task' => 'Tâches',
            'activity' => 'Activités',
            default => $module,
        };
    }

    /**
     * @return list<string>
     */
    private function textColumns(string $module): array
    {
        return match ($module) {
            'company' => ['name', 'domain', 'city', 'industry'],
            'contact' => ['first_name', 'last_name', 'email', 'job_title'],
            'deal' => ['name'],
            'task' => ['title', 'description'],
            'activity' => ['subject', 'body', 'type'],
            default => [],
        };
    }

    /**
     * @return list<string>
     */
    private function sortableColumns(string $module): array
    {
        return match ($module) {
            'company' => ['name', 'created_at'],
            'contact' => ['last_name', 'created_at'],
            'deal' => ['name', 'amount', 'expected_close_date', 'created_at'],
            'task' => ['title', 'due_at', 'created_at'],
            'activity' => ['occurred_at'],
            default => ['created_at'],
        };
    }

    /**
     * @return list<string>
     */
    private function eagerLoads(string $module): array
    {
        return match ($module) {
            'company' => ['owner:id,name'],
            'contact' => ['owner:id,name', 'company:id,name'],
            'deal' => ['owner:id,name', 'company:id,name', 'contact:id,first_name,last_name', 'stage:id,name', 'pipeline:id,name'],
            'task' => ['assignee:id,name', 'taskable'],
            'activity' => ['user:id,name', 'subjectable'],
            default => [],
        };
    }

    /**
     * Standard filter map: field => spec.
     *
     * @return array<string, array{type: string, target?: string}>
     */
    private function standardFilters(string $module): array
    {
        return match ($module) {
            'company' => [
                'name' => ['type' => 'string', 'target' => 'name'],
                'domain' => ['type' => 'string', 'target' => 'domain'],
                'industry' => ['type' => 'exact', 'target' => 'industry'],
                'city' => ['type' => 'string', 'target' => 'city'],
                'country' => ['type' => 'exact', 'target' => 'country'],
                'owner' => ['type' => 'relation', 'target' => 'owner.name'],
            ],
            'contact' => [
                'first_name' => ['type' => 'string', 'target' => 'first_name'],
                'last_name' => ['type' => 'string', 'target' => 'last_name'],
                'email' => ['type' => 'string', 'target' => 'email'],
                'job_title' => ['type' => 'string', 'target' => 'job_title'],
                'company' => ['type' => 'relation', 'target' => 'company.name'],
                'owner' => ['type' => 'relation', 'target' => 'owner.name'],
            ],
            'deal' => [
                'name' => ['type' => 'string', 'target' => 'name'],
                'status' => ['type' => 'exact', 'target' => 'status'],
                'currency' => ['type' => 'exact', 'target' => 'currency'],
                'stage' => ['type' => 'relation', 'target' => 'stage.name'],
                'pipeline' => ['type' => 'relation', 'target' => 'pipeline.name'],
                'company' => ['type' => 'relation', 'target' => 'company.name'],
                'owner' => ['type' => 'relation', 'target' => 'owner.name'],
            ],
            'task' => [
                'title' => ['type' => 'string', 'target' => 'title'],
                'priority' => ['type' => 'exact', 'target' => 'priority'],
                'assignee' => ['type' => 'relation', 'target' => 'assignee.name'],
            ],
            'activity' => [
                'type' => ['type' => 'exact', 'target' => 'type'],
                'subject' => ['type' => 'string', 'target' => 'subject'],
            ],
            default => [],
        };
    }

    private function relationName(string $target): string
    {
        return str_contains($target, '.') ? explode('.', $target, 2)[0] : $target;
    }

    private function relationColumn(string $target): string
    {
        return str_contains($target, '.') ? explode('.', $target, 2)[1] : 'name';
    }

    /**
     * @return Collection<int, CustomFieldDefinition>
     */
    private function customDefinitions(string $module): Collection
    {
        $entity = $this->entityType($module);

        return $entity === null ? collect() : $this->customFields->definitions($entity);
    }

    /**
     * @return list<string>
     */
    private function standardFieldList(string $module): array
    {
        return array_keys($this->standardFilters($module));
    }

    private function customFieldSummary(string $module): string
    {
        return $this->customDefinitions($module)->map(function (CustomFieldDefinition $def) {
            $detail = "`{$def->key}` ({$def->label}, {$def->type->value}";
            if ($def->type->hasChoices()) {
                $choices = collect($def->choices())->pluck('value')->implode(' | ');
                $detail .= $choices !== '' ? " : {$choices}" : '';
            }

            return $detail.')';
        })->implode(', ');
    }

    private function picklistValues(Picklist $list): string
    {
        return collect(app(Picklists::class)->options($list))
            ->map(fn (array $o) => $o['value'])
            ->implode(', ');
    }

    private function validModule(mixed $module): ?string
    {
        return is_string($module) && in_array($module, self::MODULES, true) ? $module : null;
    }

    private function stringOrNull(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return $value === '' ? null : $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function arrayOrEmpty(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * @return array{data: mixed, summary: string, error: bool}
     */
    private function error(string $message): array
    {
        return ['data' => ['error' => $message], 'summary' => 'Erreur', 'error' => true];
    }
}
