<?php

namespace App\Application\Picklists\UseCases;

use App\Application\Crm\Services\Picklists;
use App\Application\Picklists\Commands\UpdatePicklistOptionCommand;
use App\Domain\Picklists\Repositories\PicklistOptionRepositoryInterface;
use App\Models\PicklistOption;

class UpdatePicklistOption
{
    public function __construct(
        private readonly PicklistOptionRepositoryInterface $options,
        private readonly Picklists $picklists,
    ) {}

    public function handle(PicklistOption $option, UpdatePicklistOptionCommand $command): PicklistOption
    {
        $updated = $this->options->update($option, $command->toArray());

        $this->picklists->flush();

        return $updated;
    }
}
