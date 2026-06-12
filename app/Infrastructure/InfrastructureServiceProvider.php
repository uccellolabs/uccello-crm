<?php

namespace App\Infrastructure;

use App\Application\Assistant\Assistant;
use App\Application\Companies\Queries\GetCompanyShowPageQueryInterface;
use App\Application\Companies\Queries\ListCompaniesQueryInterface;
use App\Application\Contacts\Queries\GetContactShowPageQueryInterface;
use App\Application\Contacts\Queries\ListContactsQueryInterface;
use App\Application\Crm\Queries\GetCrmRecordFormDataQueryInterface;
use App\Application\Crm\Services\CrmFormOptions;
use App\Application\Crm\Services\CrmMorphResolver;
use App\Application\Crm\Services\CustomFields;
use App\Application\Crm\Services\Picklists;
use App\Application\CustomFields\Queries\ListCustomFieldsQueryInterface;
use App\Application\Dashboard\Queries\DashboardMetricsQueryInterface;
use App\Application\Deals\Queries\GetDealBoardQueryInterface;
use App\Application\Deals\Queries\GetDealShowPageQueryInterface;
use App\Application\Deals\Queries\GetDealStatsForCompanyQueryInterface;
use App\Application\Deals\Queries\GetDealStatsForContactQueryInterface;
use App\Application\Deals\Repositories\DealReadRepositoryInterface;
use App\Application\Crm\Repositories\CrmTimelineReadRepositoryInterface;
use App\Application\Picklists\Queries\ListPicklistAdminQueryInterface;
use App\Application\Pipelines\Queries\GetPipelineSettingsQueryInterface;
use App\Application\Pipelines\Repositories\PipelineReadRepositoryInterface;
use App\Application\Shared\Ports\AuthorizationCheckerInterface;
use App\Application\Tasks\Queries\ListTasksQueryInterface;
use App\Application\Teams\Queries\GetTeamEditPageQueryInterface;
use App\Application\Teams\Queries\ListUserTeamsQueryInterface;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Activities\Repositories\ActivityRepositoryInterface;
use App\Domain\Companies\Repositories\CompanyRepositoryInterface;
use App\Domain\Contacts\Repositories\ContactRepositoryInterface;
use App\Domain\CustomFields\Repositories\CustomFieldRepositoryInterface;
use App\Domain\Deals\Repositories\DealRepositoryInterface;
use App\Domain\Picklists\Repositories\PicklistOptionRepositoryInterface;
use App\Domain\Pipelines\Repositories\PipelineRepositoryInterface;
use App\Domain\Pipelines\Repositories\PipelineStageRepositoryInterface;
use App\Domain\Tasks\Repositories\TaskRepositoryInterface;
use App\Domain\Teams\Repositories\TeamRepositoryInterface;
use App\Infrastructure\Auth\EloquentUserRepository;
use App\Infrastructure\Assistant\CrmAssistant;
use App\Infrastructure\Authorization\GateAuthorizationChecker;
use App\Infrastructure\Teams\EloquentTeamRepository;
use App\Infrastructure\Teams\Queries\EloquentGetTeamEditPageQuery;
use App\Infrastructure\Teams\Queries\EloquentListUserTeamsQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentGetCompanyShowPageQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentGetContactShowPageQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentGetCrmRecordFormDataQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentGetDealBoardQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentGetDealShowPageQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentGetDealStatsForCompanyQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentGetDealStatsForContactQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentGetPipelineSettingsQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentDashboardMetricsQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentListCompaniesQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentListContactsQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentListCustomFieldsQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentListPicklistAdminQuery;
use App\Infrastructure\Persistence\Eloquent\Queries\EloquentListTasksQuery;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentActivityRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentCompanyRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentContactRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentCustomFieldRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentCrmTimelineReadRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentDealReadRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentDealRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentPicklistOptionRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentPipelineReadRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentPipelineRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentPipelineStageRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentTaskRepository;
use App\Infrastructure\Services\EloquentCrmFormOptions;
use App\Infrastructure\Services\EloquentCrmMorphResolver;
use App\Infrastructure\Services\EloquentCustomFields;
use App\Infrastructure\Services\EloquentPicklists;
use Illuminate\Support\ServiceProvider;

class InfrastructureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CompanyRepositoryInterface::class, EloquentCompanyRepository::class);
        $this->app->bind(ContactRepositoryInterface::class, EloquentContactRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, EloquentTaskRepository::class);
        $this->app->bind(ActivityRepositoryInterface::class, EloquentActivityRepository::class);
        $this->app->bind(DealRepositoryInterface::class, EloquentDealRepository::class);
        $this->app->bind(DealReadRepositoryInterface::class, EloquentDealReadRepository::class);
        $this->app->bind(CrmTimelineReadRepositoryInterface::class, EloquentCrmTimelineReadRepository::class);
        $this->app->bind(PipelineRepositoryInterface::class, EloquentPipelineRepository::class);
        $this->app->bind(PipelineStageRepositoryInterface::class, EloquentPipelineStageRepository::class);
        $this->app->bind(PipelineReadRepositoryInterface::class, EloquentPipelineReadRepository::class);
        $this->app->bind(CustomFieldRepositoryInterface::class, EloquentCustomFieldRepository::class);
        $this->app->bind(PicklistOptionRepositoryInterface::class, EloquentPicklistOptionRepository::class);
        $this->app->bind(TeamRepositoryInterface::class, EloquentTeamRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

        $this->app->bind(AuthorizationCheckerInterface::class, GateAuthorizationChecker::class);

        $this->app->bind(ListCompaniesQueryInterface::class, EloquentListCompaniesQuery::class);
        $this->app->bind(ListContactsQueryInterface::class, EloquentListContactsQuery::class);
        $this->app->bind(GetCompanyShowPageQueryInterface::class, EloquentGetCompanyShowPageQuery::class);
        $this->app->bind(GetContactShowPageQueryInterface::class, EloquentGetContactShowPageQuery::class);
        $this->app->bind(GetCrmRecordFormDataQueryInterface::class, EloquentGetCrmRecordFormDataQuery::class);
        $this->app->bind(ListTasksQueryInterface::class, EloquentListTasksQuery::class);
        $this->app->bind(GetDealBoardQueryInterface::class, EloquentGetDealBoardQuery::class);
        $this->app->bind(GetDealShowPageQueryInterface::class, EloquentGetDealShowPageQuery::class);
        $this->app->bind(GetDealStatsForCompanyQueryInterface::class, EloquentGetDealStatsForCompanyQuery::class);
        $this->app->bind(GetDealStatsForContactQueryInterface::class, EloquentGetDealStatsForContactQuery::class);
        $this->app->bind(GetPipelineSettingsQueryInterface::class, EloquentGetPipelineSettingsQuery::class);
        $this->app->bind(ListCustomFieldsQueryInterface::class, EloquentListCustomFieldsQuery::class);
        $this->app->bind(ListPicklistAdminQueryInterface::class, EloquentListPicklistAdminQuery::class);
        $this->app->bind(DashboardMetricsQueryInterface::class, EloquentDashboardMetricsQuery::class);
        $this->app->bind(ListUserTeamsQueryInterface::class, EloquentListUserTeamsQuery::class);
        $this->app->bind(GetTeamEditPageQueryInterface::class, EloquentGetTeamEditPageQuery::class);

        $this->app->scoped(CustomFields::class, EloquentCustomFields::class);
        $this->app->scoped(Picklists::class, EloquentPicklists::class);
        $this->app->scoped(CrmFormOptions::class, EloquentCrmFormOptions::class);
        $this->app->bind(CrmMorphResolver::class, EloquentCrmMorphResolver::class);

        $this->app->bind(Assistant::class, CrmAssistant::class);
    }
}
