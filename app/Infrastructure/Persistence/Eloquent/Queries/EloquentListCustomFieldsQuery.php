<?php

namespace App\Infrastructure\Persistence\Eloquent\Queries;

use App\Application\CustomFields\DTOs\CustomFieldsAdminPageData;
use App\Application\CustomFields\Presenters\CustomFieldPresenter;
use App\Application\CustomFields\Queries\ListCustomFieldsQueryInterface;
use App\Application\Shared\Presenters\EnumLabels;
use App\Models\CustomFieldDefinition;

class EloquentListCustomFieldsQuery implements ListCustomFieldsQueryInterface
{
    public function __construct(
        private readonly CustomFieldPresenter $presenter,
    ) {}

    public function adminPage(): CustomFieldsAdminPageData
    {
        return new CustomFieldsAdminPageData(
            definitions: $this->groupedByEntity(),
            entities: array_values(EnumLabels::crmEntityOptions()),
            fieldTypes: array_values(EnumLabels::customFieldTypeOptions()),
        );
    }

    public function groupedByEntity(): array
    {
        return CustomFieldDefinition::query()
            ->orderBy('entity_type')
            ->orderBy('position')
            ->get()
            ->map(fn (CustomFieldDefinition $definition) => $this->presenter->toItem($definition))
            ->groupBy('entity_type')
            ->map(fn ($items) => array_values($items->all()))
            ->all();
    }
}
