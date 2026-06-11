<?php

namespace App\Application\Deals\Queries;

use App\Application\Deals\DTOs\DealStatsData;
use App\Models\Company;

interface GetDealStatsForCompanyQueryInterface
{
    public function statsForCompany(Company $company): DealStatsData;
}
