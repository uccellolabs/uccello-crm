<?php

namespace App\Application\Shared\Results;

enum OperationResult
{
    case Success;
    case NotAllowed;
    case HasDependents;

    public function succeeded(): bool
    {
        return $this === self::Success;
    }
}
