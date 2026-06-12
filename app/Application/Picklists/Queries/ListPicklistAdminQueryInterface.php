<?php

namespace App\Application\Picklists\Queries;

use App\Application\Picklists\DTOs\PicklistAdminPageData;

interface ListPicklistAdminQueryInterface
{
    public function adminPage(): PicklistAdminPageData;

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function groupedByPicklist(): array;
}
