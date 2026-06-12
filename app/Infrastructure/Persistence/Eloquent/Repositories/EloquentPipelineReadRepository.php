<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Pipelines\Repositories\PipelineReadRepositoryInterface;
use App\Domain\Pipelines\Repositories\PipelineRepositoryInterface;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EloquentPipelineReadRepository implements PipelineReadRepositoryInterface
{
    public function __construct(
        private readonly PipelineRepositoryInterface $pipelines,
    ) {}

    public function resolveForRequest(?int $pipelineId): Pipeline
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user?->current_team_id) {
            $this->pipelines->ensureDefaultExists($user->current_team_id);
        }

        return Pipeline::query()
            ->when($pipelineId, fn ($query) => $query->where('id', $pipelineId))
            ->orderByDesc('is_default')
            ->orderBy('position')
            ->firstOrFail();
    }

    public function findOrFail(int $id): Pipeline
    {
        return Pipeline::query()->findOrFail($id);
    }

    public function findStageOrFail(int $stageId): PipelineStage
    {
        return PipelineStage::query()->findOrFail($stageId);
    }
}
