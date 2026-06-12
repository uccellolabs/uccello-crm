<?php

namespace App\Application\Companies\UseCases;

use App\Application\Companies\Commands\UpdateCompanyCommand;
use App\Domain\Companies\Repositories\CompanyRepositoryInterface;
use App\Models\Company;

class UpdateCompany
{
    public function __construct(
        private readonly CompanyRepositoryInterface $companies,
    ) {}

    public function handle(Company $company, UpdateCompanyCommand $command): Company
    {
        return $this->companies->update($company, $command->toArray());
    }
}
