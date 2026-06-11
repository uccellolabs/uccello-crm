<?php

namespace App\Application\Dashboard\DTOs;

use App\Domain\Shared\ValueObjects\DateRange;

final readonly class DashboardPageData
{
    public function __construct(
        public DateRange $range,
        public DashboardKpisData $kpis,
        public DashboardChartsData $charts,
        public DashboardListsData $lists,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'range' => $this->range->toDateStrings(),
            'kpis' => $this->kpis->toArray(),
            'charts' => $this->charts->toArray(),
            'lists' => $this->lists->toArray(),
        ];
    }
}
