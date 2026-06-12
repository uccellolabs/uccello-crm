<?php

namespace App\Application\Contacts\DTOs;

final readonly class ContactShowData
{
    /**
     * @param  array<string, mixed>  $contact
     */
    public function __construct(
        public array $contact,
    ) {}
}
