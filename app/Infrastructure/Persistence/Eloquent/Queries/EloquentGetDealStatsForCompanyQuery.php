<?php

namespace App\Infrastructure\Persistence\Eloquent\Queries;

use App\Application\Deals\DTOs\DealStatsData;
use App\Application\Deals\Queries\GetDealStatsForCompanyQueryInterface;
use App\Domain\Shared\Enums\DealStatus;
use App\Models\Company;

class EloquentGetDealStatsForCompanyQuery implements GetDealStatsForCompanyQueryInterface
{
    public function statsForCompany(Company $company): DealStatsData
    {
        return new DealStatsData(
            openDeals: $company->deals()->where('status', DealStatus::Open)->count(),
            pipelineValue: (float) $company->deals()->where('status', DealStatus::Open)->sum('amount'),
            wonValue: (float) $company->deals()->where('status', DealStatus::Won)->sum('amount'),
            contacts: $company->contacts()->count(),
            dealsTotal: $company->deals()->count(),
        );
    }
}
