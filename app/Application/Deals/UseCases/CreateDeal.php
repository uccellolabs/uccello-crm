<?php

namespace App\Application\Deals\UseCases;

use App\Application\Deals\Commands\CreateDealCommand;
use App\Domain\Deals\Repositories\DealRepositoryInterface;
use App\Domain\Deals\ValueObjects\DealClosure;
use App\Models\Deal;
use App\Models\PipelineStage;

class CreateDeal
{
    public function __construct(
        private readonly DealRepositoryInterface $deals,
    ) {}

    public function handle(CreateDealCommand $command, PipelineStage $stage): Deal
    {
        $closure = DealClosure::fromTerminalFlags($stage->is_won, $stage->is_lost);
        $attributes = $closure->toModelAttributes();

        $enriched = CreateDealCommand::fromForm(
            name: $command->name,
            pipelineId: $command->pipelineId,
            pipelineStageId: $command->pipelineStageId,
            position: $this->deals->nextPosition($stage->id),
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

        return $this->deals->create($enriched->toArray());
    }
}
