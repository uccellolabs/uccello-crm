<?php

namespace App\Domain\Pipelines\Services;

use App\Domain\Pipelines\Enums\PipelineStageDeletionBlockReason;

final class PipelineStageDeletionPolicy
{
    public function evaluate(bool $isWon, bool $isLost, bool $hasDeals): ?PipelineStageDeletionBlockReason
    {
        if ($isWon || $isLost) {
            return PipelineStageDeletionBlockReason::TerminalStage;
        }

        if ($hasDeals) {
            return PipelineStageDeletionBlockReason::HasDeals;
        }

        return null;
    }
}
