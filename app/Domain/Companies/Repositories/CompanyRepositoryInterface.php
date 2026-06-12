<?php

namespace App\Domain\Companies\Repositories;

use App\Models\Company;

interface CompanyRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Company;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Company $company, array $data): Company;

    public function delete(Company $company): void;
}
