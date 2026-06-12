<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Contacts\Repositories\ContactRepositoryInterface;
use App\Models\Contact;

class EloquentContactRepository implements ContactRepositoryInterface
{
    public function create(array $data): Contact
    {
        return Contact::create($data);
    }

    public function update(Contact $contact, array $data): Contact
    {
        $contact->update($data);

        return $contact->fresh();
    }

    public function delete(Contact $contact): void
    {
        $contact->delete();
    }
}
