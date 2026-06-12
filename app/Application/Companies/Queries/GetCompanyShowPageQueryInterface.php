<?php

namespace App\Application\Companies\Queries;

use App\Application\Companies\DTOs\CompaniesPageData;
use App\Application\Companies\DTOs\CompanyShowPageData;
use App\Models\Company;
use App\Models\User;

interface GetCompanyShowPageQueryInterface
{
    public function forCompany(User $user, Company $company): CompanyShowPageData;
}
