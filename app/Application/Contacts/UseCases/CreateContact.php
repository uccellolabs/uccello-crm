<?php

namespace App\Application\Contacts\UseCases;

use App\Application\Contacts\Commands\CreateContactCommand;
use App\Domain\Contacts\Repositories\ContactRepositoryInterface;
use App\Models\Contact;

class CreateContact
{
    public function __construct(
        private readonly ContactRepositoryInterface $contacts,
    ) {}

    public function handle(CreateContactCommand $command): Contact
    {
        return $this->contacts->create($command->toArray());
    }
}
