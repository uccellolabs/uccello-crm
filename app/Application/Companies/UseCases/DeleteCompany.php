<?php

namespace App\Application\Companies\UseCases;

use App\Domain\Companies\Repositories\CompanyRepositoryInterface;
use App\Models\Company;

class DeleteCompany
{
    public function __construct(
        private readonly CompanyRepositoryInterface $companies,
    ) {}

    public function handle(Company $company): void
    {
        $this->companies->delete($company);
    }
}
