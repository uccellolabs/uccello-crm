<?php

namespace App\Domain\Pipelines\Enums;

enum PipelineStageDeletionBlockReason
{
    case TerminalStage;
    case HasDeals;
}
