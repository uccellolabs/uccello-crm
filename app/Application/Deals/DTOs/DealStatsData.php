<?php

namespace App\Application\Deals\DTOs;

final readonly class DealStatsData
{
    public function __construct(
        public int $openDeals,
        public float $pipelineValue,
        public float $wonValue,
        public ?int $contacts = null,
        public ?int $dealsTotal = null,
        public ?int $deals = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'open_deals' => $this->openDeals,
            'pipeline_value' => $this->pipelineValue,
            'won_value' => $this->wonValue,
        ];

        if ($this->contacts !== null) {
            $data['contacts'] = $this->contacts;
        }

        if ($this->dealsTotal !== null) {
            $data['deals_total'] = $this->dealsTotal;
        }

        if ($this->deals !== null) {
            $data['deals'] = $this->deals;
        }

        return $data;
    }
}
