<?php

namespace App\Infrastructure\Persistence\Eloquent\Queries;

use App\Application\Pipelines\DTOs\PipelineSettingsData;
use App\Application\Pipelines\Queries\GetPipelineSettingsQueryInterface;
use App\Domain\Pipelines\Repositories\PipelineRepositoryInterface;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EloquentGetPipelineSettingsQuery implements GetPipelineSettingsQueryInterface
{
    public function __construct(
        private readonly PipelineRepositoryInterface $pipelines,
    ) {}

    public function all(): PipelineSettingsData
    {
        /** @var User $user */
        $user = Auth::user();

        $this->pipelines->ensureDefaultExists($user->current_team_id);

        $pipelines = Pipeline::query()
            ->with(['stages' => fn ($query) => $query->withCount('deals')])
            ->orderBy('position')
            ->get()
            ->map(fn (Pipeline $pipeline) => [
                'id' => $pipeline->id,
                'name' => $pipeline->name,
                'stages' => $pipeline->stages->map(fn (PipelineStage $stage) => [
                    'id' => $stage->id,
                    'name' => $stage->name,
                    'color' => $stage->color,
                    'position' => $stage->position,
                    'probability' => $stage->probability,
                    'is_won' => $stage->is_won,
                    'is_lost' => $stage->is_lost,
                    'deals_count' => $stage->deals_count,
                ])->values()->all(),
            ])
            ->values()
            ->all();

        return new PipelineSettingsData(pipelines: array_values($pipelines));
    }
}
