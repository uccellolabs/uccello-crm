<?php

namespace App\Application\CustomFields\UseCases;

use App\Application\CustomFields\Commands\UpdateCustomFieldCommand;
use App\Domain\CustomFields\Repositories\CustomFieldRepositoryInterface;
use App\Models\CustomFieldDefinition;

class UpdateCustomField
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $customFields,
    ) {}

    public function handle(CustomFieldDefinition $definition, UpdateCustomFieldCommand $command): CustomFieldDefinition
    {
        return $this->customFields->update($definition, $command->toArray());
    }
}
