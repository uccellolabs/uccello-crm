<?php

namespace App\Application\CustomFields\UseCases;

use App\Domain\CustomFields\Repositories\CustomFieldRepositoryInterface;
use App\Models\CustomFieldDefinition;

class DeleteCustomField
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $customFields,
    ) {}

    public function handle(CustomFieldDefinition $definition): void
    {
        $this->customFields->delete($definition);
    }
}
