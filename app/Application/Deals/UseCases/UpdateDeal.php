<?php

namespace App\Application\Deals\UseCases;

use App\Domain\Deals\ValueObjects\DealClosure;
use App\Models\Deal;
use App\Models\PipelineStage;

class UpdateDeal
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Deal $deal, array $data, PipelineStage $stage): Deal
    {
        $data = [...$data, ...DealClosure::fromTerminalFlags($stage->is_won, $stage->is_lost, $deal->closed_at)->toModelAttributes()];

        $deal->update($data);

        return $deal->fresh();
    }
}
