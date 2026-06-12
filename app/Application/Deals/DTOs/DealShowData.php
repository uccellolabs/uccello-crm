<?php

namespace App\Application\Deals\DTOs;

final readonly class DealShowData
{
    /**
     * @param  array<string, mixed>  $stats
     * @param  list<array<string, mixed>>  $stages
     */
    public function __construct(
        public array $stats,
        public array $stages,
    ) {}
}
