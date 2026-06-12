<?php

namespace App\Application\Companies\Queries;

use App\Application\Companies\DTOs\CompaniesPageData;
use App\Models\User;

interface ListCompaniesQueryInterface
{
    public function paginate(User $user, string $search): CompaniesPageData;
}
