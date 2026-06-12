<?php

namespace App\Application\Contacts\DTOs;

final readonly class ContactsPageData
{
    /**
     * @param  array{search: string}  $filters
     * @param  array{create: bool}  $can
     */
    public function __construct(
        public mixed $contacts,
        public array $filters,
        public array $can,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'contacts' => $this->contacts,
            'filters' => $this->filters,
            'can' => $this->can,
        ];
    }
}
