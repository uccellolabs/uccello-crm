<?php

namespace App\Application\Shared\Commands;

final readonly class ReorderIdsCommand
{
    /**
     * @param  list<int>  $ids
     */
    public function __construct(
        public array $ids,
    ) {}
}
