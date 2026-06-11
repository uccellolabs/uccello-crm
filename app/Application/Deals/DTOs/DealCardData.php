<?php

namespace App\Application\Deals\DTOs;

final readonly class DealCardData
{
    /**
     * @param  array{id: int, name: string}|null  $company
     * @param  array{id: int, name: string}|null  $contact
     * @param  array{id: int, name: string}|null  $owner
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?float $amount,
        public string $currency,
        public int $position,
        public ?array $company,
        public ?array $contact,
        public ?array $owner,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'position' => $this->position,
            'company' => $this->company,
            'contact' => $this->contact,
            'owner' => $this->owner,
        ];
    }
}
