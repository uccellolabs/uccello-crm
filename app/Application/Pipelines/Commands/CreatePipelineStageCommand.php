<?php

namespace App\Application\Pipelines\Commands;

final readonly class CreatePipelineStageCommand
{
    public function __construct(
        public int $pipelineId,
        public string $name,
        public ?string $color = null,
        public ?int $probability = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'pipeline_id' => $this->pipelineId,
            'name' => $this->name,
            'color' => $this->color,
            'probability' => $this->probability,
        ], fn ($value) => $value !== null);
    }
}
