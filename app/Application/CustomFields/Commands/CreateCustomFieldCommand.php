<?php

namespace App\Application\CustomFields\Commands;

use App\Domain\Shared\Enums\CustomFieldType;

final readonly class CreateCustomFieldCommand
{
    /**
     * @param  array<int, array{value: string, label: string}>  $choicePairs
     */
    public function __construct(
        public string $entityType,
        public string $label,
        public CustomFieldType $type,
        public string $key,
        public int $position,
        public array $choicePairs,
        public bool $isRequired,
        public bool $isFilterable,
        public ?string $relatedModule = null,
        public ?string $helpText = null,
    ) {}

    /**
     * @param  array<int, array{value: string, label: string}>  $choicePairs
     */
    public static function fromForm(
        string $entityType,
        string $label,
        CustomFieldType $type,
        array $choicePairs,
        bool $isRequired,
        bool $isFilterable,
        ?string $relatedModule = null,
        ?string $helpText = null,
    ): self {
        return new self(
            entityType: $entityType,
            label: $label,
            type: $type,
            key: '',
            position: 0,
            choicePairs: $choicePairs,
            isRequired: $isRequired,
            isFilterable: $isFilterable,
            relatedModule: $relatedModule,
            helpText: $helpText,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'entity_type' => $this->entityType,
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type,
            'options' => match (true) {
                $this->type->hasChoices() => ['choices' => $this->choicePairs],
                $this->type === CustomFieldType::Relation => ['related_module' => $this->relatedModule],
                default => null,
            },
            'is_required' => $this->isRequired,
            'is_filterable' => $this->isFilterable,
            'help_text' => $this->helpText,
            'position' => $this->position,
        ];
    }
}
