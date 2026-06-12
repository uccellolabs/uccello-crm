<?php

namespace App\Application\Companies\DTOs;

final readonly class CompanyShowData
{
    /**
     * @param  array<string, mixed>  $company
     * @param  list<array<string, mixed>>  $contacts
     */
    public function __construct(
        public array $company,
        public array $contacts,
    ) {}
}
