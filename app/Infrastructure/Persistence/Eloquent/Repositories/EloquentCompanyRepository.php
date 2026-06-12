<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Companies\Repositories\CompanyRepositoryInterface;
use App\Models\Company;

class EloquentCompanyRepository implements CompanyRepositoryInterface
{
    public function create(array $data): Company
    {
        return Company::create($data);
    }

    public function update(Company $company, array $data): Company
    {
        $company->update($data);

        return $company->fresh();
    }

    public function delete(Company $company): void
    {
        $company->delete();
    }
}
