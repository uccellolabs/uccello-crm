<?php

namespace App\Http\Controllers;

use App\Application\Dashboard\Queries\DashboardMetricsQueryInterface;
use App\Domain\Shared\ValueObjects\DateRange;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardMetricsQueryInterface $metrics,
    ) {}

    /**
     * Render the CRM dashboard scoped to a date range.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Company::class);

        $page = $this->metrics->forRange(DateRange::fromStrings(
            $request->string('from')->toString(),
            $request->string('to')->toString(),
        ));

        return Inertia::render('Dashboard', $page->toArray());
    }
}
