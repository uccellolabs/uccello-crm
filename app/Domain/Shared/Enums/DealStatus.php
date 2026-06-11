<?php

namespace App\Domain\Shared\Enums;

enum DealStatus: string
{
    case Open = 'open';
    case Won = 'won';
    case Lost = 'lost';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Open => __('Open'),
            self::Won => __('Won'),
            self::Lost => __('Lost'),
        };
    }
}
