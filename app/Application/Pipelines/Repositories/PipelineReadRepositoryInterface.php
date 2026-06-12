<?php

namespace App\Application\Pipelines\Repositories;

use App\Models\Pipeline;
use App\Models\PipelineStage;

interface PipelineReadRepositoryInterface
{
    public function resolveForRequest(?int $pipelineId): Pipeline;

    public function findOrFail(int $id): Pipeline;

    public function findStageOrFail(int $stageId): PipelineStage;
}
