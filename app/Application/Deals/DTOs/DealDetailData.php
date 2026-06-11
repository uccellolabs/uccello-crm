<?php

namespace App\Application\Deals\DTOs;

final readonly class DealDetailData
{
    /**
     * @param  array{id: int, name: string, color: string}  $stage
     * @param  array{id: int, name: string}|null  $company
     * @param  array{id: int, name: string}|null  $contact
     * @param  array{id: int, name: string}|null  $owner
     * @param  array<string, mixed>  $customFields
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?float $amount,
        public string $currency,
        public string $status,
        public string $statusLabel,
        public int $pipelineId,
        public int $pipelineStageId,
        public array $stage,
        public ?int $companyId,
        public ?array $company,
        public ?int $contactId,
        public ?array $contact,
        public ?int $ownerId,
        public ?array $owner,
        public ?string $expectedCloseDate,
        public array $customFields,
        public ?string $createdAt,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'status_label' => $this->statusLabel,
            'pipeline_id' => $this->pipelineId,
            'pipeline_stage_id' => $this->pipelineStageId,
            'stage' => $this->stage,
            'company_id' => $this->companyId,
            'company' => $this->company,
            'contact_id' => $this->contactId,
            'contact' => $this->contact,
            'owner_id' => $this->ownerId,
            'owner' => $this->owner,
            'expected_close_date' => $this->expectedCloseDate,
            'custom_fields' => $this->customFields,
            'created_at' => $this->createdAt,
        ];
    }
}
