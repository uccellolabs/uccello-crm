<?php

namespace App\Application\CustomFields\Presenters;

use App\Application\Shared\Presenters\EnumLabels;
use App\Models\CustomFieldDefinition;

class CustomFieldPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function toItem(CustomFieldDefinition $definition): array
    {
        return [
            'id' => $definition->id,
            'entity_type' => $definition->entity_type,
            'key' => $definition->key,
            'label' => $definition->label,
            'type' => $definition->type->value,
            'type_label' => EnumLabels::customFieldType($definition->type),
            'choices' => collect($definition->choices())->pluck('label')->all(),
            'related_module' => $definition->options['related_module'] ?? null,
            'is_required' => $definition->is_required,
            'is_filterable' => $definition->is_filterable,
            'help_text' => $definition->help_text,
            'position' => $definition->position,
        ];
    }
}
