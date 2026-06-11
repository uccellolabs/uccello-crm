<?php

namespace App\Application\Deals\UseCases;

use App\Domain\Deals\Repositories\DealRepositoryInterface;
use App\Domain\Deals\ValueObjects\DealClosure;
use App\Models\Deal;
use App\Models\PipelineStage;

class CreateDeal
{
    public function __construct(
        private readonly DealRepositoryInterface $deals,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, PipelineStage $stage): Deal
    {
        $data['position'] = $this->deals->nextPosition($stage->id);
        $data = [...$data, ...DealClosure::fromTerminalFlags($stage->is_won, $stage->is_lost)->toModelAttributes()];

        return Deal::create($data);
    }
}
