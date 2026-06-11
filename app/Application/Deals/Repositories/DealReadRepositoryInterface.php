<?php

namespace App\Application\Deals\Repositories;

use App\Models\Deal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface DealReadRepositoryInterface
{
    /** @return Collection<int, Deal> */
    public function summariesForRecord(Model $record, int $limit = 6): Collection;

    /** @return Collection<int, Deal> */
    public function forBoardStage(int $stageId): Collection;
}
