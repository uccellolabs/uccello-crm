<?php

namespace App\Application\Dashboard\Queries;

use App\Application\Dashboard\DTOs\DashboardPageData;
use App\Domain\Shared\ValueObjects\DateRange;

interface DashboardMetricsQueryInterface
{
    public function forRange(DateRange $range): DashboardPageData;
}
