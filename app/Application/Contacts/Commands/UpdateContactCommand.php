<?php

namespace App\Application\Contacts\Commands;

final readonly class UpdateContactCommand
{
    /**
     * @param  array<string, mixed>|null  $customFields
     */
    public function __construct(
        public string $firstName,
        public string $lastName,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $jobTitle = null,
        public ?int $companyId = null,
        public ?int $ownerId = null,
        public ?array $customFields = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return (new CreateContactCommand(
            firstName: $this->firstName,
            lastName: $this->lastName,
            email: $this->email,
            phone: $this->phone,
            jobTitle: $this->jobTitle,
            companyId: $this->companyId,
            ownerId: $this->ownerId,
            customFields: $this->customFields,
        ))->toArray();
    }
}
