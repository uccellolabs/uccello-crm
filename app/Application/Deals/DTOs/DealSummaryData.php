<?php

namespace App\Application\Deals\DTOs;

final readonly class DealSummaryData
{
    /**
     * @param  array{name: string, color: string}  $stage
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?float $amount,
        public string $status,
        public array $stage,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'status' => $this->status,
            'stage' => $this->stage,
        ];
    }
}
