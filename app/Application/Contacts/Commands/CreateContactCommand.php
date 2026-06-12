<?php

namespace App\Application\Contacts\Commands;

final readonly class CreateContactCommand
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
        return array_filter([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'job_title' => $this->jobTitle,
            'company_id' => $this->companyId,
            'owner_id' => $this->ownerId,
            'custom_fields' => $this->customFields,
        ], fn ($value) => $value !== null);
    }
}
