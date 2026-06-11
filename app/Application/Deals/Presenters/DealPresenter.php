<?php

namespace App\Application\Deals\Presenters;

use App\Application\Deals\DTOs\DealCardData;
use App\Application\Deals\DTOs\DealDetailData;
use App\Application\Deals\DTOs\DealSummaryData;
use App\Models\Deal;

class DealPresenter
{
    public function card(Deal $deal): DealCardData
    {
        return new DealCardData(
            id: $deal->id,
            name: $deal->name,
            amount: $deal->amount !== null ? (float) $deal->amount : null,
            currency: $deal->currency,
            position: $deal->position,
            company: $deal->company ? ['id' => $deal->company->id, 'name' => $deal->company->name] : null,
            contact: $deal->contact ? ['id' => $deal->contact->id, 'name' => $deal->contact->full_name] : null,
            owner: $deal->owner ? ['id' => $deal->owner->id, 'name' => $deal->owner->name] : null,
        );
    }

    public function detail(Deal $deal): DealDetailData
    {
        return new DealDetailData(
            id: $deal->id,
            name: $deal->name,
            amount: $deal->amount !== null ? (float) $deal->amount : null,
            currency: $deal->currency,
            status: $deal->status->value,
            statusLabel: $deal->status->label(),
            pipelineId: $deal->pipeline_id,
            pipelineStageId: $deal->pipeline_stage_id,
            stage: ['id' => $deal->stage->id, 'name' => $deal->stage->name, 'color' => $deal->stage->color],
            companyId: $deal->company_id,
            company: $deal->company ? ['id' => $deal->company->id, 'name' => $deal->company->name] : null,
            contactId: $deal->contact_id,
            contact: $deal->contact ? ['id' => $deal->contact->id, 'name' => $deal->contact->full_name] : null,
            ownerId: $deal->owner_id,
            owner: $deal->owner ? ['id' => $deal->owner->id, 'name' => $deal->owner->name] : null,
            expectedCloseDate: $deal->expected_close_date?->toDateString(),
            customFields: $deal->custom_fields ?? [],
            createdAt: $deal->created_at?->toISOString(),
        );
    }

    public function summary(Deal $deal): DealSummaryData
    {
        return new DealSummaryData(
            id: $deal->id,
            name: $deal->name,
            amount: $deal->amount !== null ? (float) $deal->amount : null,
            status: $deal->status->value,
            stage: ['name' => $deal->stage->name, 'color' => $deal->stage->color],
        );
    }
}
