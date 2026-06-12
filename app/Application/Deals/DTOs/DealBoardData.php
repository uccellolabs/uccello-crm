<?php

namespace App\Application\Deals\DTOs;

final readonly class DealBoardData
{
    /**
     * @param  array{id: int, name: string}  $pipeline
     * @param  list<array{id: int, name: string}>  $pipelines
     * @param  list<array<string, mixed>>  $stages
     * @param  array{manage: bool}  $can
     */
    public function __construct(
        public array $pipeline,
        public array $pipelines,
        public array $stages,
        public array $can,
    ) {}
}
