<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Pipelines\UseCases\CreateDefaultPipeline;
use App\Domain\Pipelines\Repositories\PipelineRepositoryInterface;
use App\Models\Pipeline;
use App\Models\Team;
use Illuminate\Support\Facades\Cache;

class EloquentPipelineRepository implements PipelineRepositoryInterface
{
    public function __construct(
        private readonly CreateDefaultPipeline $createDefaultPipeline,
    ) {}

    public function ensureDefaultExists(int $teamId): void
    {
        if (Pipeline::query()->exists()) {
            return;
        }

        Cache::lock("default-pipeline:{$teamId}", 10)->block(10, function () use ($teamId) {
            if (! Pipeline::query()->exists()) {
                $team = Team::query()->findOrFail($teamId);
                $this->createDefaultPipeline->handle($team);
            }
        });
    }
}
