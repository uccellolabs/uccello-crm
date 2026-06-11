<?php

namespace App\Application\Deals\UseCases;

use App\Domain\Deals\Repositories\DealRepositoryInterface;
use App\Domain\Deals\ValueObjects\DealClosure;
use App\Models\Deal;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\DB;

class MoveDeal
{
    public function __construct(
        private readonly DealRepositoryInterface $deals,
    ) {}

    /**
     * Move a deal to a stage at a given position, renumbering siblings and
     * updating won/lost status when the target stage is terminal.
     */
    public function handle(Deal $deal, PipelineStage $stage, int $position): Deal
    {
        return DB::transaction(function () use ($deal, $stage, $position) {
            $fromStageId = $deal->pipeline_stage_id;

            $closure = DealClosure::fromTerminalFlags($stage->is_won, $stage->is_lost, $deal->closed_at);
            $attributes = $closure->toModelAttributes();

            $deal->pipeline_stage_id = $stage->id;
            $deal->status = $attributes['status'];
            $deal->closed_at = $attributes['closed_at'];
            $deal->save();

            $this->deals->resequence($stage->id, $deal->id, $position);

            if ($fromStageId !== $stage->id) {
                $this->deals->resequence($fromStageId);
            }

            return $deal->fresh();
        });
    }
}
