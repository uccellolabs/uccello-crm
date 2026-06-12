<?php

namespace App\Application\Contacts\UseCases;

use App\Application\Contacts\Commands\UpdateContactCommand;
use App\Domain\Contacts\Repositories\ContactRepositoryInterface;
use App\Models\Contact;

class UpdateContact
{
    public function __construct(
        private readonly ContactRepositoryInterface $contacts,
    ) {}

    public function handle(Contact $contact, UpdateContactCommand $command): Contact
    {
        return $this->contacts->update($contact, $command->toArray());
    }
}
