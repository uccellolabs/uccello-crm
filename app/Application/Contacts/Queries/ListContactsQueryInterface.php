<?php

namespace App\Application\Contacts\Queries;

use App\Application\Contacts\DTOs\ContactsPageData;
use App\Models\User;

interface ListContactsQueryInterface
{
    public function paginate(User $user, string $search): ContactsPageData;
}
