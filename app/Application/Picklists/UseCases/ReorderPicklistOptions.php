<?php

namespace App\Application\Picklists\UseCases;

use App\Application\Shared\Commands\ReorderIdsCommand;
use App\Domain\Picklists\Repositories\PicklistOptionRepositoryInterface;

class ReorderPicklistOptions
{
    public function __construct(
        private readonly PicklistOptionRepositoryInterface $options,
    ) {}

    public function handle(ReorderIdsCommand $command): void
    {
        $this->options->reorder($command->ids);
    }
}
