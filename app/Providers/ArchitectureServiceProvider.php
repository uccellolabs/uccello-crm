<?php

namespace App\Providers;

use App\Application\Assistant\Assistant;
use App\Application\Crm\Services\CrmFormOptions;
use App\Application\Crm\Services\CustomFields;
use App\Application\Crm\Services\Picklists;
use App\Application\Dashboard\Queries\DashboardMetricsQueryInterface;
use App\Application\Deals\Queries\GetDealStatsForCompanyQueryInterface;
use App\Application\Deals\Queries\GetDealStatsForContactQueryInterface;
use App\Application\Deals\Repositories\DealReadRepositoryInterface;
use App\Application\Pipelines\Repositories\PipelineReadRepositoryInterface;
use App\Domain\Deals\Repositories\DealRepositoryInterface;
use App\Domain\Pipelines\Repositories\PipelineRepositoryInterface;
use App\Infrastructure\Assistant\CrmAssistant;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentDashboardMetricsQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentGetDealStatsForCompanyQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentGetDealStatsForContactQuery;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentDealReadRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentDealRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentPipelineReadRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentPipelineRepository;
use App\Infrastructure\Services\EloquentCrmFormOptions;
use App\Infrastructure\Services\EloquentCustomFields;
use App\Infrastructure\Services\EloquentPicklists;
use Illuminate\Support\ServiceProvider;

/**
 * Binds the Application/Domain ports to their Eloquent / framework
 * implementations in the Infrastructure layer — the single place where the
 * dependency-inversion wiring lives.
 */
class ArchitectureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories & queries (read/write split).
        $this->app->bind(DealRepositoryInterface::class, EloquentDealRepository::class);
        $this->app->bind(DealReadRepositoryInterface::class, EloquentDealReadRepository::class);
        $this->app->bind(GetDealStatsForCompanyQueryInterface::class, EloquentGetDealStatsForCompanyQuery::class);
        $this->app->bind(GetDealStatsForContactQueryInterface::class, EloquentGetDealStatsForContactQuery::class);
        $this->app->bind(PipelineRepositoryInterface::class, EloquentPipelineRepository::class);
        $this->app->bind(PipelineReadRepositoryInterface::class, EloquentPipelineReadRepository::class);
        $this->app->bind(DashboardMetricsQueryInterface::class, EloquentDashboardMetricsQuery::class);

        // CRM read/validation services — scoped to share their per-request cache.
        $this->app->scoped(CustomFields::class, EloquentCustomFields::class);
        $this->app->scoped(Picklists::class, EloquentPicklists::class);
        $this->app->scoped(CrmFormOptions::class, EloquentCrmFormOptions::class);

        // AI assistant (Laravel AI SDK adapter).
        $this->app->bind(Assistant::class, CrmAssistant::class);
    }
}
