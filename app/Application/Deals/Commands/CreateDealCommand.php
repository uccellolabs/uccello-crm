<?php

namespace App\Application\Deals\Commands;

use App\Domain\Shared\Enums\DealStatus;
use Carbon\CarbonInterface;

final readonly class CreateDealCommand
{
    /**
     * @param  array<string, mixed>|null  $customFields
     */
    public function __construct(
        public string $name,
        public int $pipelineId,
        public int $pipelineStageId,
        public int $position,
        public DealStatus $status,
        public CarbonInterface|string|null $closedAt,
        public ?float $amount = null,
        public ?string $currency = null,
        public ?int $companyId = null,
        public ?int $contactId = null,
        public ?string $expectedCloseDate = null,
        public ?int $ownerId = null,
        public ?array $customFields = null,
    ) {}

    /**
     * @param  array<string, mixed>|null  $customFields
     */
    public static function fromFormInput(
        string $name,
        int $pipelineId,
        int $pipelineStageId,
        ?float $amount = null,
        ?string $currency = null,
        ?int $companyId = null,
        ?int $contactId = null,
        ?string $expectedCloseDate = null,
        ?int $ownerId = null,
        ?array $customFields = null,
    ): self {
        return new self(
            name: $name,
            pipelineId: $pipelineId,
            pipelineStageId: $pipelineStageId,
            position: 0,
            status: DealStatus::Open,
            closedAt: null,
            amount: $amount,
            currency: $currency,
            companyId: $companyId,
            contactId: $contactId,
            expectedCloseDate: $expectedCloseDate,
            ownerId: $ownerId,
            customFields: $customFields,
        );
    }

    /**
     * @param  array<string, mixed>|null  $customFields
     */
    public static function fromForm(
        string $name,
        int $pipelineId,
        int $pipelineStageId,
        int $position,
        DealStatus $status,
        CarbonInterface|string|null $closedAt,
        ?float $amount = null,
        ?string $currency = null,
        ?int $companyId = null,
        ?int $contactId = null,
        ?string $expectedCloseDate = null,
        ?int $ownerId = null,
        ?array $customFields = null,
    ): self {
        return new self(
            name: $name,
            pipelineId: $pipelineId,
            pipelineStageId: $pipelineStageId,
            position: $position,
            status: $status,
            closedAt: $closedAt,
            amount: $amount,
            currency: $currency,
            companyId: $companyId,
            contactId: $contactId,
            expectedCloseDate: $expectedCloseDate,
            ownerId: $ownerId,
            customFields: $customFields,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'pipeline_id' => $this->pipelineId,
            'pipeline_stage_id' => $this->pipelineStageId,
            'position' => $this->position,
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
