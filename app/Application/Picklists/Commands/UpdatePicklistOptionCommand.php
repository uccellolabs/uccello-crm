<?php

namespace App\Application\Picklists\Commands;

final readonly class UpdatePicklistOptionCommand
{
    public function __construct(
        public string $label,
        public ?string $color = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'color' => $this->color,
        ];
    }
}
