<?php

namespace App\Http\Requests\Crm;

use App\Domain\Shared\Enums\CrmEntity;
use App\Domain\Shared\Enums\CustomFieldType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCustomFieldRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
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
     * The choices as a normalized list of {value, label} pairs.
     *
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
}
