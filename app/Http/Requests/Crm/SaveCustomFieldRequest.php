<?php

namespace App\Http\Requests\Crm;

use App\Application\CustomFields\Commands\CreateCustomFieldCommand;
use App\Application\CustomFields\Commands\UpdateCustomFieldCommand;
use App\Domain\Shared\Enums\CrmEntity;
use App\Domain\Shared\Enums\CustomFieldType;
use App\Models\CustomFieldDefinition;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCustomFieldRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $entities = array_map(fn (CrmEntity $entity) => $entity->value, CrmEntity::withCustomFieldForms());

        $needsChoices = in_array($this->input('type'), [
            CustomFieldType::Select->value,
            CustomFieldType::MultiSelect->value,
        ], true);

        $isRelation = $this->input('type') === CustomFieldType::Relation->value;

        return [
            'entity_type' => ['required', Rule::in($entities)],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(CustomFieldType::class)],
            'choices' => [$needsChoices ? 'required' : 'nullable', 'array'],
            'choices.*' => ['required', 'string', 'max:255'],
            'related_module' => [
                $isRelation ? 'required' : 'nullable',
                Rule::in(['company', 'contact', 'deal']),
            ],
            'is_required' => ['boolean'],
            'is_filterable' => ['boolean'],
            'help_text' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function choicePairs(): array
    {
        /** @var array<int, string> $choices */
        $choices = (array) ($this->validated('choices') ?? []);

        return collect($choices)
            ->map(fn (string $choice) => trim($choice))
            ->filter()
            ->unique()
            ->map(fn (string $choice) => ['value' => $choice, 'label' => $choice])
            ->values()
            ->all();
    }

    public function toCreateCommand(): CreateCustomFieldCommand
    {
        $data = $this->validated();

        return CreateCustomFieldCommand::fromForm(
            entityType: $data['entity_type'],
            label: $data['label'],
            type: CustomFieldType::from($data['type']),
            choicePairs: $this->choicePairs(),
            isRequired: $this->boolean('is_required'),
            isFilterable: $this->boolean('is_filterable'),
            relatedModule: $data['related_module'] ?? null,
            helpText: $data['help_text'] ?? null,
        );
    }

    public function toUpdateCommand(CustomFieldDefinition $definition): UpdateCustomFieldCommand
    {
        $data = $this->validated();

        return UpdateCustomFieldCommand::fromDefinition(
            definition: $definition,
            label: $data['label'],
            choicePairs: $this->choicePairs(),
            isRequired: $this->boolean('is_required'),
            isFilterable: $this->boolean('is_filterable'),
            helpText: $data['help_text'] ?? null,
        );
    }
}
