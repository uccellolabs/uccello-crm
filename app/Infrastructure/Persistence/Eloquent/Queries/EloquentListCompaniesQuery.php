<?php

namespace App\Infrastructure\Persistence\Eloquent\Queries;

use App\Application\Companies\DTOs\CompaniesPageData;
use App\Application\Companies\Presenters\CompanyPresenter;
use App\Application\Companies\Queries\ListCompaniesQueryInterface;
use App\Application\Shared\Ports\AuthorizationCheckerInterface;
use App\Models\Company;
use App\Models\User;

class EloquentListCompaniesQuery implements ListCompaniesQueryInterface
{
    public function __construct(
        private readonly CompanyPresenter $presenter,
        private readonly AuthorizationCheckerInterface $authorization,
    ) {}

    public function paginate(User $user, string $search): CompaniesPageData
    {
        $companies = Company::query()
            ->with('owner:id,name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'ilike', "%{$search}%")
                        ->orWhere('domain', 'ilike', "%{$search}%")
                        ->orWhere('city', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Company $company) => $this->presenter->toListItem($company));

        return new CompaniesPageData(
            companies: $companies,
            filters: ['search' => $search],
            can: ['create' => $this->authorization->can($user, 'create', Company::class)],
        );
    }
}
