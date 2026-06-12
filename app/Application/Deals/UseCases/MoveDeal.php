<?php

namespace App\Application\Deals\UseCases;

use App\Application\Deals\Commands\MoveDealCommand;
use App\Application\Pipelines\Repositories\PipelineReadRepositoryInterface;
use App\Domain\Deals\Repositories\DealRepositoryInterface;
use App\Models\Deal;

class MoveDeal
{
    public function __construct(
        private readonly DealRepositoryInterface $deals,
        private readonly PipelineReadRepositoryInterface $pipelineRead,
    ) {}

    /**
     * Move a deal to a stage at a given position, renumbering siblings and
     * updating won/lost status when the target stage is terminal.
     */
    public function handle(Deal $deal, MoveDealCommand $command): Deal
    {
        $stage = $this->pipelineRead->findStageOrFail($command->stageId);

        return $this->deals->moveToStage(
            $deal,
            $stage->id,
            $stage->is_won,
            $stage->is_lost,
            $command->position,
        );
    }
}
