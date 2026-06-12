<?php

namespace App\Application\CustomFields\UseCases;

use App\Application\Shared\Commands\ReorderIdsCommand;
use App\Domain\CustomFields\Repositories\CustomFieldRepositoryInterface;

class ReorderCustomFields
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $customFields,
    ) {}

    public function handle(ReorderIdsCommand $command): void
    {
        $this->customFields->reorder($command->ids);
    }
}
