<?php

namespace App\Domain\Pipelines\Repositories;

interface PipelineRepositoryInterface
{
    public function ensureDefaultExists(int $teamId): void;

    public function createDefault(int $teamId): \App\Models\Pipeline;
}
