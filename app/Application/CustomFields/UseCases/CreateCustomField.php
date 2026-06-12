<?php

namespace App\Application\CustomFields\UseCases;

use App\Application\CustomFields\Commands\CreateCustomFieldCommand;
use App\Domain\CustomFields\Repositories\CustomFieldRepositoryInterface;
use App\Domain\Shared\ValueObjects\UniqueSlug;
use App\Models\CustomFieldDefinition;

class CreateCustomField
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $customFields,
    ) {}

    public function handle(CreateCustomFieldCommand $command): CustomFieldDefinition
    {
        $key = UniqueSlug::generate(
            $command->label,
            fn (string $slug) => $this->customFields->keyExists($command->entityType, $slug),
            'field',
        );

        return $this->customFields->create(new CreateCustomFieldCommand(
            entityType: $command->entityType,
            label: $command->label,
            type: $command->type,
            key: $key->value,
            position: $this->customFields->nextPosition($command->entityType),
            choicePairs: $command->choicePairs,
            isRequired: $command->isRequired,
            isFilterable: $command->isFilterable,
            relatedModule: $command->relatedModule,
            helpText: $command->helpText,
        )->toArray());
    }
}
