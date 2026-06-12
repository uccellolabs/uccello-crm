<?php

namespace App\Application\Companies\DTOs;

final readonly class CompaniesPageData
{
    /**
     * @param  array{search: string}  $filters
     * @param  array{create: bool}  $can
     */
    public function __construct(
        public mixed $companies,
        public array $filters,
        public array $can,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'companies' => $this->companies,
            'filters' => $this->filters,
            'can' => $this->can,
        ];
    }
}
