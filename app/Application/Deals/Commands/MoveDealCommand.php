<?php

namespace App\Application\Deals\Commands;

final readonly class MoveDealCommand
{
    public function __construct(
        public int $stageId,
        public int $position,
    ) {}
}
