<?php

namespace App\Application\Deals\Commands;

use App\Domain\Shared\Enums\DealStatus;

final readonly class UpdateDealCommand
{
    /**
     * @param  array<string, mixed>|null  $customFields
     */
    public function __construct(
        public string $name,
        public int $pipelineId,
        public int $pipelineStageId,
        public DealStatus $status,
        public mixed $closedAt,
        public ?float $amount = null,
        public ?string $currency = null,
        public ?int $companyId = null,
        public ?int $contactId = null,
        public ?string $expectedCloseDate = null,
        public ?int $ownerId = null,
        public ?array $customFields = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'pipeline_id' => $this->pipelineId,
            'pipeline_stage_id' => $this->pipelineStageId,
            'status' => $this->status,
            'closed_at' => $this->closedAt,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'company_id' => $this->companyId,
            'contact_id' => $this->contactId,
            'expected_close_date' => $this->expectedCloseDate,
            'owner_id' => $this->ownerId,
            'custom_fields' => $this->customFields,
        ], fn ($value) => $value !== null);
    }
}
