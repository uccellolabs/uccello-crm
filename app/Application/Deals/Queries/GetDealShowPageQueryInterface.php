<?php

namespace App\Application\Deals\Queries;

use App\Application\Deals\DTOs\DealShowPageData;
use App\Models\Deal;
use App\Models\User;

interface GetDealShowPageQueryInterface
{
    public function forDeal(User $user, Deal $deal): DealShowPageData;
}
