<?php

namespace App\Application\CustomFields\Commands;

use App\Domain\Shared\Enums\CustomFieldType;
use App\Models\CustomFieldDefinition;

final readonly class UpdateCustomFieldCommand
{
    /**
     * @param  array<int, array{value: string, label: string}>  $choicePairs
     * @param  array<string, mixed>|null  $existingOptions
     */
    public function __construct(
        public string $label,
        public array $choicePairs,
        public bool $isRequired,
        public bool $isFilterable,
        public CustomFieldType $type,
        public ?array $existingOptions,
        public ?string $helpText = null,
    ) {}

    /**
     * @param  array<int, array{value: string, label: string}>  $choicePairs
     */
    public static function fromDefinition(
        CustomFieldDefinition $definition,
        string $label,
        array $choicePairs,
        bool $isRequired,
        bool $isFilterable,
        ?string $helpText,
    ): self {
        return new self(
            label: $label,
            choicePairs: $choicePairs,
            isRequired: $isRequired,
            isFilterable: $isFilterable,
            type: $definition->type,
            existingOptions: $definition->options,
            helpText: $helpText,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'options' => match (true) {
                $this->type->hasChoices() => ['choices' => $this->choicePairs],
                $this->type === CustomFieldType::Relation => $this->existingOptions,
                default => null,
            },
            'is_required' => $this->isRequired,
            'is_filterable' => $this->isFilterable,
            'help_text' => $this->helpText,
        ];
    }
}
