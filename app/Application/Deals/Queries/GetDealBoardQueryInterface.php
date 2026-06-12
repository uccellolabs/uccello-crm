<?php

namespace App\Application\Deals\Queries;

use App\Application\Deals\DTOs\DealBoardData;
use App\Models\Deal;
use App\Models\User;

interface GetDealBoardQueryInterface
{
    public function forPipeline(User $user, int $pipelineId): DealBoardData;
}
