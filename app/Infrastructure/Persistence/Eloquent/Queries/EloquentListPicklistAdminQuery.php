<?php

namespace App\Infrastructure\Persistence\Eloquent\Queries;

use App\Application\Crm\Services\Picklists;
use App\Application\Picklists\DTOs\PicklistAdminPageData;
use App\Application\Picklists\Queries\ListPicklistAdminQueryInterface;
use App\Application\Shared\Presenters\EnumLabels;
use App\Domain\Shared\Enums\Picklist;
use App\Models\PicklistOption;

class EloquentListPicklistAdminQuery implements ListPicklistAdminQueryInterface
{
    public function __construct(
        private readonly Picklists $picklists,
    ) {}

    public function adminPage(): PicklistAdminPageData
    {
        return new PicklistAdminPageData(
            options: $this->groupedByPicklist(),
            picklists: array_values(EnumLabels::picklistOptions()),
        );
    }

    public function groupedByPicklist(): array
    {
        $options = [];

        foreach (Picklist::cases() as $list) {
            $options[$list->value] = array_values($this->picklists->rows($list)
                ->map(fn (PicklistOption $option) => [
                    'id' => $option->id,
                    'value' => $option->value,
                    'label' => $option->label,
                    'color' => $option->color,
                    'position' => $option->position,
                    'is_system' => $option->is_system,
                ])
                ->all());
        }

        return $options;
    }
}
