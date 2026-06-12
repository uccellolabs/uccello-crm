<?php

namespace App\Application\Pipelines\UseCases;

use App\Application\Shared\Results\DeletionResult;
use App\Domain\Pipelines\Enums\PipelineStageDeletionBlockReason;
use App\Domain\Pipelines\Repositories\PipelineStageRepositoryInterface;
use App\Domain\Pipelines\Services\PipelineStageDeletionPolicy;
use App\Models\PipelineStage;

class DeletePipelineStage
{
    public function __construct(
        private readonly PipelineStageRepositoryInterface $stages,
        private readonly PipelineStageDeletionPolicy $deletionPolicy,
    ) {}

    public function handle(PipelineStage $stage): DeletionResult
    {
        $blockReason = $this->deletionPolicy->evaluate(
            $stage->is_won,
            $stage->is_lost,
            $this->stages->hasDeals($stage->id),
        );

        if ($blockReason !== null) {
            return match ($blockReason) {
                PipelineStageDeletionBlockReason::TerminalStage => DeletionResult::BlockedTerminalStage,
                PipelineStageDeletionBlockReason::HasDeals => DeletionResult::BlockedHasDeals,
            };
        }

        $this->stages->delete($stage);

        return DeletionResult::Success;
    }
}
