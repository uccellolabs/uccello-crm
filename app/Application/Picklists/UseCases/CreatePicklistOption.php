<?php

namespace App\Application\Picklists\UseCases;

use App\Application\Crm\Services\Picklists;
use App\Application\Picklists\Commands\CreatePicklistOptionCommand;
use App\Domain\Picklists\Repositories\PicklistOptionRepositoryInterface;
use App\Domain\Shared\ValueObjects\UniqueSlug;
use App\Models\PicklistOption;

class CreatePicklistOption
{
    public function __construct(
        private readonly PicklistOptionRepositoryInterface $options,
        private readonly Picklists $picklists,
    ) {}

    public function handle(CreatePicklistOptionCommand $command): PicklistOption
    {
        $slug = UniqueSlug::generate(
            $command->label,
            fn (string $value) => $this->options->valueExists($command->picklist, $value),
        );

        $option = $this->options->create(new CreatePicklistOptionCommand(
            picklist: $command->picklist,
            label: $command->label,
            value: $slug->value,
            position: $this->options->nextPosition($command->picklist),
            color: $command->color,
        )->toArray());

        $this->picklists->flush();

        return $option;
    }
}
