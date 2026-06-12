<?php

namespace App\Application\Companies\Commands;

final readonly class UpdateCompanyCommand
{
    /**
     * @param  array<string, mixed>|null  $customFields
     */
    public function __construct(
        public string $name,
        public ?string $domain = null,
        public ?string $industry = null,
        public ?string $phone = null,
        public ?string $website = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $postalCode = null,
        public ?string $country = null,
        public ?int $ownerId = null,
        public ?array $customFields = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return (new CreateCompanyCommand(
            name: $this->name,
            domain: $this->domain,
            industry: $this->industry,
            phone: $this->phone,
            website: $this->website,
            address: $this->address,
            city: $this->city,
            postalCode: $this->postalCode,
            country: $this->country,
            ownerId: $this->ownerId,
            customFields: $this->customFields,
        ))->toArray();
    }
}
