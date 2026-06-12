<?php

namespace App\Application\Picklists\Commands;

use App\Domain\Shared\Enums\Picklist;

final readonly class CreatePicklistOptionCommand
{
    public function __construct(
        public Picklist $picklist,
        public string $label,
        public string $value,
        public int $position,
        public ?string $color = null,
        public bool $isSystem = false,
    ) {}

    public static function fromForm(Picklist $picklist, string $label, ?string $color): self
    {
        return new self(
            picklist: $picklist,
            label: $label,
            value: '',
            position: 0,
            color: $color,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'picklist' => $this->picklist->value,
            'value' => $this->value,
            'label' => $this->label,
            'color' => $this->color,
            'position' => $this->position,
            'is_system' => $this->isSystem,
        ];
    }
}
