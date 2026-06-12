<?php

namespace App\Domain\Shared\Exceptions;

use RuntimeException;

final class InvalidReorderException extends RuntimeException
{
    public static function idCountMismatch(): self
    {
        return new self('Reorder ids do not match existing records.');
    }
}
