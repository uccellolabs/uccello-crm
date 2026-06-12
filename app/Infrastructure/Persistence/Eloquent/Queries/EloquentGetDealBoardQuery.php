<?php

namespace App\Infrastructure\Persistence\Eloquent\Queries;

use App\Application\Deals\DTOs\DealBoardData;
use App\Application\Deals\Presenters\DealPresenter;
use App\Application\Deals\Queries\GetDealBoardQueryInterface;
use App\Application\Shared\Ports\AuthorizationCheckerInterface;
use App\Domain\Pipelines\Repositories\PipelineRepositoryInterface;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;

class EloquentGetDealBoardQuery implements GetDealBoardQueryInterface
{
    public function __construct(
        private readonly DealPresenter $dealPresenter,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly PipelineRepositoryInterface $pipelines,
    ) {}

    public function forPipeline(User $user, int $pipelineId): DealBoardData
    {
        $pipeline = Pipeline::query()->findOrFail($pipelineId);

        $this->pipelines->ensureDefaultExists($pipeline->team_id);

        $stages = $pipeline->stages()
            ->with(['deals' => fn ($query) => $query
                ->with(['company:id,name', 'contact:id,first_name,last_name', 'owner:id,name'])
                ->orderBy('position'),
            ])
            ->get()
            ->map(fn (PipelineStage $stage) => [
                'id' => $stage->id,
                'name' => $stage->name,
                'key' => $stage->key,
                'color' => $stage->color,
                'is_won' => $stage->is_won,
                'is_lost' => $stage->is_lost,
                'total_amount' => (float) $stage->deals->sum(fn (Deal $deal) => (float) $deal->amount),
                'deals' => $stage->deals
                    ->map(fn (Deal $deal) => $this->dealPresenter->card($deal)->toArray())
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        $pipelines = Pipeline::query()->orderBy('position')->get(['id', 'name'])
            ->map(fn (Pipeline $p) => ['id' => $p->id, 'name' => $p->name])
            ->values()
            ->all();

        return new DealBoardData(
            pipeline: ['id' => $pipeline->id, 'name' => $pipeline->name],
            pipelines: array_values($pipelines),
            stages: array_values($stages),
            can: [
                'manage' => $this->authorization->can($user, 'create', Deal::class),
            ],
        );
    }
}
