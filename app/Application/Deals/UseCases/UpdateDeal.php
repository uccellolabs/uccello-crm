<?php

namespace App\Application\Deals\UseCases;

use App\Application\Deals\Commands\UpdateDealCommand;
use App\Domain\Deals\Repositories\DealRepositoryInterface;
use App\Domain\Deals\ValueObjects\DealClosure;
use App\Models\Deal;
use App\Models\PipelineStage;

class UpdateDeal
{
    public function __construct(
        private readonly DealRepositoryInterface $deals,
    ) {}

    public function handle(Deal $deal, UpdateDealCommand $command, PipelineStage $stage): Deal
    {
        $closure = DealClosure::fromTerminalFlags($stage->is_won, $stage->is_lost, $deal->closed_at);
        $attributes = $closure->toModelAttributes();

        $enriched = new UpdateDealCommand(
            name: $command->name,
            pipelineId: $command->pipelineId,
            pipelineStageId: $command->pipelineStageId,
            status: $attributes['status'],
            closedAt: $attributes['closed_at'],
            amount: $command->amount,
            currency: $command->currency,
            companyId: $command->companyId,
            contactId: $command->contactId,
            expectedCloseDate: $command->expectedCloseDate,
            ownerId: $command->ownerId,
            customFields: $command->customFields,
        );

        return $this->deals->update($deal, $enriched->toArray());
    }
}
