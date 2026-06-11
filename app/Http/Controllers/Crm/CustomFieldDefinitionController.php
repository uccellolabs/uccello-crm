<?php

namespace App\Http\Controllers\Crm;

use App\Application\Admin\UseCases\ReorderByIds;
use App\Domain\Shared\Enums\CrmEntity;
use App\Domain\Shared\Enums\CustomFieldType;
use App\Domain\Shared\ValueObjects\UniqueSlug;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\SaveCustomFieldRequest;
use App\Models\CustomFieldDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CustomFieldDefinitionController extends Controller
{
    public function __construct(
        private readonly ReorderByIds $reorderByIds,
    ) {}

    /**
     * Display the custom-field administration screen.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', CustomFieldDefinition::class);

        $definitions = CustomFieldDefinition::query()
            ->orderBy('entity_type')
            ->orderBy('position')
            ->get()
            ->map(fn (CustomFieldDefinition $definition) => $this->toItem($definition))
            ->groupBy('entity_type');

        return Inertia::render('crm/custom-fields/Index', [
            'definitions' => $definitions,
            'entities' => CrmEntity::options(),
            'fieldTypes' => CustomFieldType::options(),
        ]);
    }

    /**
     * Store a new custom field definition.
     */
    public function store(SaveCustomFieldRequest $request): RedirectResponse
    {
        Gate::authorize('create', CustomFieldDefinition::class);

        $validated = $request->validated();
        $type = CustomFieldType::from($validated['type']);
        $entityType = $validated['entity_type'];

        $key = UniqueSlug::generate(
            $validated['label'],
            fn (string $slug) => CustomFieldDefinition::query()
                ->where('entity_type', $entityType)
                ->where('key', $slug)
                ->exists(),
            'field',
        );

        CustomFieldDefinition::create([
            'entity_type' => $entityType,
            'key' => $key->value,
            'label' => $validated['label'],
            'type' => $type,
            'options' => match (true) {
                $type->hasChoices() => ['choices' => $request->choicePairs()],
                $type === CustomFieldType::Relation => ['related_module' => $validated['related_module']],
                default => null,
            },
            'is_required' => $request->boolean('is_required'),
            'is_filterable' => $request->boolean('is_filterable'),
            'help_text' => $validated['help_text'] ?? null,
            'position' => $this->nextPosition($entityType),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Custom field created.')]);

        return back();
    }

    /**
     * Update a custom field definition. The type and key are immutable to
     * preserve the values already stored against the field.
     */
    public function update(SaveCustomFieldRequest $request, CustomFieldDefinition $customField): RedirectResponse
    {
        Gate::authorize('update', $customField);

        $customField->update([
            'label' => $request->validated('label'),
            'options' => match (true) {
                $customField->type->hasChoices() => ['choices' => $request->choicePairs()],
                $customField->type === CustomFieldType::Relation => $customField->options,
                default => null,
            },
            'is_required' => $request->boolean('is_required'),
            'is_filterable' => $request->boolean('is_filterable'),
            'help_text' => $request->validated('help_text'),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Custom field updated.')]);

        return back();
    }

    /**
     * Delete a custom field definition.
     */
    public function destroy(CustomFieldDefinition $customField): RedirectResponse
    {
        Gate::authorize('delete', $customField);

        $customField->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Custom field deleted.')]);

        return back();
    }

    /**
     * Persist a new ordering of fields within an entity.
     */
    public function reorder(Request $request): RedirectResponse
    {
        Gate::authorize('create', CustomFieldDefinition::class);

        $ids = array_values($request->collect('ids')->map(fn ($id) => (int) $id)->all());

        $this->reorderByIds->handle(CustomFieldDefinition::class, $ids);

        return back();
    }

    /**
     * The next position within an entity's field list.
     */
    protected function nextPosition(string $entityType): int
    {
        return (int) CustomFieldDefinition::query()
            ->forEntity($entityType)
            ->max('position') + 1;
    }

    /**
     * Serialize a definition for the admin screen.
     *
     * @return array<string, mixed>
     */
    protected function toItem(CustomFieldDefinition $definition): array
    {
        return [
            'id' => $definition->id,
            'entity_type' => $definition->entity_type,
            'key' => $definition->key,
            'label' => $definition->label,
            'type' => $definition->type->value,
            'type_label' => $definition->type->label(),
            'choices' => collect($definition->choices())->pluck('label')->all(),
            'related_module' => $definition->options['related_module'] ?? null,
            'is_required' => $definition->is_required,
            'is_filterable' => $definition->is_filterable,
            'help_text' => $definition->help_text,
            'position' => $definition->position,
        ];
    }
}
