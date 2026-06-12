<?php

namespace App\Infrastructure\Persistence\Eloquent\Queries;

use App\Application\Companies\DTOs\CompanyShowPageData;
use App\Application\Companies\Presenters\CompanyPresenter;
use App\Application\Companies\Queries\GetCompanyShowPageQueryInterface;
use App\Application\Crm\Presenters\CrmRecordShowPresenter;
use App\Application\Deals\Queries\GetDealStatsForCompanyQueryInterface;
use App\Application\Shared\Ports\AuthorizationCheckerInterface;
use App\Models\Company;
use App\Models\User;

class EloquentGetCompanyShowPageQuery implements GetCompanyShowPageQueryInterface
{
    public function __construct(
        private readonly CompanyPresenter $presenter,
        private readonly GetDealStatsForCompanyQueryInterface $dealStats,
        private readonly CrmRecordShowPresenter $showPresenter,
        private readonly AuthorizationCheckerInterface $authorization,
    ) {}

    public function forCompany(User $user, Company $company): CompanyShowPageData
    {
        $company->load('owner:id,name');

        $contacts = array_values($company->contacts()
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email', 'job_title'])
            ->map(fn ($contact) => [
                'id' => $contact->id,
                'full_name' => $contact->full_name,
                'email' => $contact->email,
                'job_title' => $contact->job_title,
            ])
            ->all());

        $sidebar = $this->showPresenter->sidebar($user, $company, 'company');

        return new CompanyShowPageData(
            company: $this->presenter->toDetail($company),
            stats: $this->dealStats->statsForCompany($company)->toArray(),
            contacts: $contacts,
            deals: array_map(
                fn ($summary) => $summary->toArray(),
                $this->showPresenter->dealsForRecord($company),
            ),
            activities: $sidebar->activities,
            tasks: $sidebar->tasks,
            members: array_map(fn ($member) => $member->toArray(), $sidebar->members),
            activityTypes: $sidebar->activityTypes,
            taskPriorities: $sidebar->taskPriorities,
            customFields: $sidebar->customFields,
            can: [
                'update' => $this->authorization->can($user, 'update', $company),
                'delete' => $this->authorization->can($user, 'delete', $company),
            ],
        );
    }
}
