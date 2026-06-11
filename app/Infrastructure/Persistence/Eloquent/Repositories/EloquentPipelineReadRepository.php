<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Pipelines\Repositories\PipelineReadRepositoryInterface;
use App\Models\Pipeline;
use App\Models\PipelineStage;

class EloquentPipelineReadRepository implements PipelineReadRepositoryInterface
{
    public function resolveForRequest(?int $pipelineId): Pipeline
    {
        return Pipeline::query()
            ->when($pipelineId, fn ($query) => $query->where('id', $pipelineId))
            ->orderByDesc('is_default')
            ->orderBy('position')
            ->firstOrFail();
    }

    public function findStageOrFail(int $stageId): PipelineStage
    {
        return PipelineStage::query()->findOrFail($stageId);
    }
}
