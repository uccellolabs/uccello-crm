<?php

namespace App\Application\Contacts\Queries;

use App\Application\Contacts\DTOs\ContactShowPageData;
use App\Models\Contact;
use App\Models\User;

interface GetContactShowPageQueryInterface
{
    public function forContact(User $user, Contact $contact): ContactShowPageData;
}
