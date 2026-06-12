<?php

namespace App\Application\Pipelines\DTOs;

final readonly class PipelineSettingsData
{
    /**
     * @param  list<array<string, mixed>>  $pipelines
     */
    public function __construct(
        public array $pipelines,
    ) {}
}
