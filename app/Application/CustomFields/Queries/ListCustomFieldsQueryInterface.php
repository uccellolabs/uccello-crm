<?php

namespace App\Application\CustomFields\Queries;

use App\Application\CustomFields\DTOs\CustomFieldsAdminPageData;

interface ListCustomFieldsQueryInterface
{
    public function adminPage(): CustomFieldsAdminPageData;

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function groupedByEntity(): array;
}
