<?php

namespace App\Application\Shared\Results;

enum DeletionResult
{
    case Success;
    case BlockedTerminalStage;
    case BlockedHasDeals;

    public function succeeded(): bool
    {
        return $this === self::Success;
    }
}
