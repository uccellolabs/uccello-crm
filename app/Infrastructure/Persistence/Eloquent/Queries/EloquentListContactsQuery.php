<?php

namespace App\Infrastructure\Persistence\Eloquent\Queries;

use App\Application\Contacts\DTOs\ContactsPageData;
use App\Application\Contacts\Presenters\ContactPresenter;
use App\Application\Contacts\Queries\ListContactsQueryInterface;
use App\Application\Shared\Ports\AuthorizationCheckerInterface;
use App\Models\Contact;
use App\Models\User;

class EloquentListContactsQuery implements ListContactsQueryInterface
{
    public function __construct(
        private readonly ContactPresenter $presenter,
        private readonly AuthorizationCheckerInterface $authorization,
    ) {}

    public function paginate(User $user, string $search): ContactsPageData
    {
        $contacts = Contact::query()
            ->with(['company:id,name', 'owner:id,name'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'ilike', "%{$search}%")
                        ->orWhere('last_name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Contact $contact) => $this->presenter->toListItem($contact));

        return new ContactsPageData(
            contacts: $contacts,
            filters: ['search' => $search],
            can: ['create' => $this->authorization->can($user, 'create', Contact::class)],
        );
    }
}
