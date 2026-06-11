<?php

namespace App\Infrastructure\Services;

use App\Application\Crm\Services\CustomFields;
use App\Domain\Shared\Enums\CrmEntity;
use App\Domain\Shared\Enums\CustomFieldType;
use App\Models\Company;
use App\Models\Contact;
use App\Models\CustomFieldDefinition;
use App\Models\Deal;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Eloquent-backed {@see CustomFields}: loads team-defined custom field
 * definitions, derives server-side validation rules, normalizes submitted
 * values before they hit the jsonb column, and serializes definitions for the
 * frontend renderer.
 *
 * Bound as a scoped singleton so the per-request definition cache is shared
 * across the rules/normalize/serialize calls of a single request.
 */
class EloquentCustomFields implements CustomFields
{
    /**
     * Per-request cache of definitions keyed by entity type.
     *
     * @var array<string, Collection<int, CustomFieldDefinition>>
     */
    protected array $cache = [];

    public function definitions(string $entityType): Collection
    {
        return $this->cache[$entityType] ??= CustomFieldDefinition::query()
            ->forEntity($entityType)
            ->get();
    }

    public function rules(string $entityType): array
    {
        $rules = [];

        foreach ($this->definitions($entityType) as $definition) {
            $key = "custom_fields.{$definition->key}";
            $required = $definition->is_required;
            $base = [$required ? 'required' : 'nullable'];
            $choices = $this->choiceValues($definition);

            $rules[$key] = match ($definition->type) {
                CustomFieldType::Textarea => [...$base, 'string', 'max:10000'],
                CustomFieldType::Number => [...$base, 'numeric'],
                CustomFieldType::Date => [...$base, 'date'],
                CustomFieldType::Email => [...$base, 'email', 'max:255'],
                CustomFieldType::Url => [...$base, 'url', 'max:2000'],
                CustomFieldType::Checkbox => $required
                    ? ['accepted']
                    : ['nullable', 'boolean'],
                CustomFieldType::Select => [...$base, Rule::in($choices)],
                CustomFieldType::MultiSelect => [...$base, 'array'],
                CustomFieldType::Relation => [...$base, 'integer', $this->relationExistsRule($definition)],
                default => [...$base, 'string', 'max:1000'],
            };

            if ($definition->type === CustomFieldType::MultiSelect) {
                $rules["{$key}.*"] = [Rule::in($choices)];
            }
        }

        return $rules;
    }

    public function normalize(string $entityType, ?array $input): array
    {
        $input ??= [];
        $output = [];

        foreach ($this->definitions($entityType) as $definition) {
            if (! array_key_exists($definition->key, $input)) {
                continue;
            }

            $value = $input[$definition->key];

            $output[$definition->key] = match ($definition->type) {
                CustomFieldType::Number => is_numeric($value) ? $value + 0 : null,
                CustomFieldType::Checkbox => (bool) $value,
                CustomFieldType::Date => $value ? Carbon::parse($value)->toDateString() : null,
                CustomFieldType::Relation => is_numeric($value) ? (int) $value : null,
                CustomFieldType::MultiSelect => array_values(array_filter(
                    (array) $value,
                    fn ($item) => $item !== null && $item !== '',
                )),
                default => ($value === '' ? null : $value),
            };
        }

        return $output;
    }

    public function forFrontend(string $entityType): array
    {
        return array_values($this->definitions($entityType)
            ->map(fn (CustomFieldDefinition $definition) => [
                'id' => $definition->id,
                'key' => $definition->key,
                'label' => $definition->label,
                'type' => $definition->type->value,
                'options' => $this->frontendOptions($definition),
                'is_required' => $definition->is_required,
                'is_filterable' => $definition->is_filterable,
                'position' => $definition->position,
                'help_text' => $definition->help_text,
            ])
            ->all());
    }

    /**
     * The options payload sent to the renderer. Relation fields get the
     * records of their target module injected as choices so both the form
     * select and the read-only label resolution work without extra queries
     * on the frontend side.
     *
     * @return array<string, mixed>
     */
    protected function frontendOptions(CustomFieldDefinition $definition): array
    {
        $options = $definition->options ?? [];

        if ($definition->type === CustomFieldType::Relation) {
            $options['choices'] = $this->relationChoices(
                (string) ($options['related_module'] ?? ''),
            );
        }

        return $options;
    }

    /**
     * The records of a relation field's target module as select options,
     * tenant-scoped by the models' team scope.
     *
     * @return array<int, array{value: int, label: string}>
     */
    protected function relationChoices(string $module): array
    {
        return match (CrmEntity::tryFrom($module)) {
            CrmEntity::Company => Company::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Company $company) => ['value' => $company->id, 'label' => $company->name])
                ->all(),
            CrmEntity::Contact => Contact::query()
                ->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name'])
                ->map(fn (Contact $contact) => ['value' => $contact->id, 'label' => $contact->full_name])
                ->all(),
            CrmEntity::Deal => Deal::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Deal $deal) => ['value' => $deal->id, 'label' => $deal->name])
                ->all(),
            default => [],
        };
    }

    /**
     * A tenant-safe existence check for a relation value: the target record
     * must live in the field's module AND in the current team (the models'
     * global team scope applies to the lookup).
     */
    protected function relationExistsRule(CustomFieldDefinition $definition): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($definition): void {
            $entity = CrmEntity::tryFrom((string) ($definition->options['related_module'] ?? ''));

            $exists = match ($entity) {
                CrmEntity::Company => Company::query()->whereKey((int) $value)->exists(),
                CrmEntity::Contact => Contact::query()->whereKey((int) $value)->exists(),
                CrmEntity::Deal => Deal::query()->whereKey((int) $value)->exists(),
                default => false,
            };

            if (! $exists) {
                $fail(__('validation.exists', ['attribute' => $definition->label]));
            }
        };
    }

    /**
     * Allowed values for a select/multiselect definition.
     *
     * @return array<int, string>
     */
    protected function choiceValues(CustomFieldDefinition $definition): array
    {
        return collect($definition->choices())
            ->pluck('value')
            ->map(fn ($value) => (string) $value)
            ->all();
    }
}
