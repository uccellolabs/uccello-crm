<?php

namespace App\Application\Dashboard\DTOs;

final readonly class DashboardChartsData
{
    /**
     * @param  list<array<string, mixed>>  $weekly
     * @param  list<array<string, mixed>>  $byStage
     */
    public function __construct(
        public array $weekly,
        public array $byStage,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'weekly' => $this->weekly,
            'by_stage' => $this->byStage,
        ];
    }
}
