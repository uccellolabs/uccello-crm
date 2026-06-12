<?php

namespace App\Http\Controllers;

use App\Application\Dashboard\Queries\DashboardMetricsQueryInterface;
use App\Http\Requests\DashboardRequest;
use App\Models\Company;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardMetricsQueryInterface $metrics,
    ) {}

    public function index(DashboardRequest $request): Response
    {
        Gate::authorize('viewAny', Company::class);

        $page = $this->metrics->forRange($request->dateRange());

        return Inertia::render('Dashboard', $page->toArray());
    }
}
