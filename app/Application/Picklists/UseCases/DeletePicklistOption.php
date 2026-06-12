<?php

namespace App\Application\Picklists\UseCases;

use App\Application\Crm\Services\Picklists;
use App\Domain\Picklists\Repositories\PicklistOptionRepositoryInterface;
use App\Models\PicklistOption;

class DeletePicklistOption
{
    public function __construct(
        private readonly PicklistOptionRepositoryInterface $options,
        private readonly Picklists $picklists,
    ) {}

    public function handle(PicklistOption $option): void
    {
        $this->options->delete($option);
        $this->picklists->flush();
    }
}
