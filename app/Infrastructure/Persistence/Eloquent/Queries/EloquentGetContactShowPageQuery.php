<?php

namespace App\Infrastructure\Persistence\Eloquent\Queries;

use App\Application\Contacts\DTOs\ContactShowPageData;
use App\Application\Contacts\Presenters\ContactPresenter;
use App\Application\Contacts\Queries\GetContactShowPageQueryInterface;
use App\Application\Crm\Presenters\CrmRecordShowPresenter;
use App\Application\Deals\Queries\GetDealStatsForContactQueryInterface;
use App\Application\Shared\Ports\AuthorizationCheckerInterface;
use App\Models\Contact;
use App\Models\User;

class EloquentGetContactShowPageQuery implements GetContactShowPageQueryInterface
{
    public function __construct(
        private readonly ContactPresenter $presenter,
        private readonly GetDealStatsForContactQueryInterface $dealStats,
        private readonly CrmRecordShowPresenter $showPresenter,
        private readonly AuthorizationCheckerInterface $authorization,
    ) {}

    public function forContact(User $user, Contact $contact): ContactShowPageData
    {
        $contact->load(['company:id,name', 'owner:id,name']);
        $sidebar = $this->showPresenter->sidebar($user, $contact, 'contact');

        return new ContactShowPageData(
            contact: $this->presenter->toDetail($contact),
            stats: $this->dealStats->statsForContact($contact)->toArray(),
            deals: array_map(
                fn ($summary) => $summary->toArray(),
                $this->showPresenter->dealsForRecord($contact),
            ),
            activities: $sidebar->activities,
            tasks: $sidebar->tasks,
            members: array_map(fn ($member) => $member->toArray(), $sidebar->members),
            activityTypes: $sidebar->activityTypes,
            taskPriorities: $sidebar->taskPriorities,
            customFields: $sidebar->customFields,
            can: [
                'update' => $this->authorization->can($user, 'update', $contact),
                'delete' => $this->authorization->can($user, 'delete', $contact),
            ],
        );
    }
}
