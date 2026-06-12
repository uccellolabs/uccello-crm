<?php

namespace App\Domain\Contacts\Repositories;

use App\Models\Contact;

interface ContactRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Contact;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Contact $contact, array $data): Contact;

    public function delete(Contact $contact): void;
}
