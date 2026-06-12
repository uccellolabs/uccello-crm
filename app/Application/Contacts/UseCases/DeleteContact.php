<?php

namespace App\Application\Contacts\UseCases;

use App\Domain\Contacts\Repositories\ContactRepositoryInterface;
use App\Models\Contact;

class DeleteContact
{
    public function __construct(
        private readonly ContactRepositoryInterface $contacts,
    ) {}

    public function handle(Contact $contact): void
    {
        $this->contacts->delete($contact);
    }
}
