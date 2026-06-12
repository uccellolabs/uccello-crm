<?php

namespace App\Infrastructure\Persistence\Eloquent\Queries;

use App\Application\Companies\Presenters\CompanyPresenter;
use App\Application\Contacts\Presenters\ContactPresenter;
use App\Application\Crm\Queries\GetCrmRecordFormDataQueryInterface;
use App\Application\Crm\Services\CrmFormOptions;
use App\Application\Crm\Services\CustomFields;
use App\Application\Crm\Services\Picklists;
use App\Application\Deals\Presenters\DealPresenter;
use App\Domain\Pipelines\Repositories\PipelineRepositoryInterface;
use App\Domain\Shared\Enums\Picklist;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;

class EloquentGetCrmRecordFormDataQuery implements GetCrmRecordFormDataQueryInterface
{
    public function __construct(
        private readonly CrmFormOptions $formOptions,
        private readonly CustomFields $customFields,
        private readonly Picklists $picklists,
        private readonly CompanyPresenter $companyPresenter,
        private readonly ContactPresenter $contactPresenter,
        private readonly DealPresenter $dealPresenter,
        private readonly PipelineRepositoryInterface $pipelines,
    ) {}

    public function forCompanyCreate(User $user): array
    {
        return [
            'owners' => $this->owners($user),
            'industries' => $this->picklists->options(Picklist::Industry),
            'customFields' => $this->customFields->forFrontend('company'),
        ];
    }

    public function forCompanyEdit(User $user, Company $company): array
    {
        $company->load('owner:id,name');

        return [
            ...$this->forCompanyCreate($user),
            'company' => $this->companyPresenter->toDetail($company),
        ];
    }

    public function forContactCreate(User $user, ?int $companyId): array
    {
        return [
            'owners' => $this->owners($user),
            'companies' => $this->companies(),
            'companyId' => $companyId,
            'customFields' => $this->customFields->forFrontend('contact'),
        ];
    }

    public function forContactEdit(User $user, Contact $contact): array
    {
        $contact->load(['company:id,name', 'owner:id,name']);

        return [
            ...$this->forContactCreate($user, null),
            'contact' => $this->contactPresenter->toDetail($contact),
        ];
    }

    public function forDealCreate(User $user, ?int $stageId, ?int $companyId, ?int $contactId): array
    {
        return [
            ...$this->dealFormFields($user),
            'stageId' => $stageId,
            'companyId' => $companyId,
            'contactId' => $contactId,
        ];
    }

    public function forDealEdit(User $user, Deal $deal): array
    {
        return [
            ...$this->dealFormFields($user),
            'deal' => $this->dealPresenter->detail($deal)->toArray(),
        ];
    }

    public function forTaskCreate(User $user): array
    {
        return [
            'assignees' => $this->owners($user),
            'taskPriorities' => $this->picklists->options(Picklist::TaskPriority),
            'relatable' => [
                'company' => $this->companies(),
                'contact' => $this->contacts(),
                'deal' => $this->deals(),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function dealFormFields(User $user): array
    {
        $this->pipelines->ensureDefaultExists($user->current_team_id);

        return [
            'pipelines' => $this->formOptions->pipelinesWithStages(),
            'companies' => $this->companies(),
            'contacts' => $this->contacts(),
            'owners' => $this->owners($user),
            'customFields' => $this->customFields->forFrontend('deal'),
        ];
    }

    /** @return list<array{value: int|string, label: string}> */
    private function owners(User $user): array
    {
        return array_map(
            fn ($option) => $option->toArray(),
            $this->formOptions->owners($user),
        );
    }

    /** @return list<array{value: int|string, label: string}> */
    private function companies(): array
    {
        return array_map(
            fn ($option) => $option->toArray(),
            $this->formOptions->companies(),
        );
    }

    /** @return list<array{value: int|string, label: string}> */
    private function contacts(): array
    {
        return array_map(
            fn ($option) => $option->toArray(),
            $this->formOptions->contacts(),
        );
    }

    /** @return list<array{value: int|string, label: string}> */
    private function deals(): array
    {
        return array_map(
            fn ($option) => $option->toArray(),
            $this->formOptions->deals(),
        );
    }
}
