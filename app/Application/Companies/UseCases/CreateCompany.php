<?php

namespace App\Application\Companies\UseCases;

use App\Application\Companies\Commands\CreateCompanyCommand;
use App\Domain\Companies\Repositories\CompanyRepositoryInterface;
use App\Models\Company;

class CreateCompany
{
    public function __construct(
        private readonly CompanyRepositoryInterface $companies,
    ) {}

    public function handle(CreateCompanyCommand $command): Company
    {
        return $this->companies->create($command->toArray());
    }
}
