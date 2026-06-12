<?php

namespace App\Application\Pipelines\Commands;

final readonly class UpdatePipelineStageCommand
{
    public function __construct(
        public string $name,
        public ?string $color = null,
        public ?int $probability = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'color' => $this->color,
            'probability' => $this->probability,
        ], fn ($value) => $value !== null);
    }
}
