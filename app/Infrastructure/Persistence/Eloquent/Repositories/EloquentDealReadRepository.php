<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Deals\Repositories\DealReadRepositoryInterface;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EloquentDealReadRepository implements DealReadRepositoryInterface
{
    public function summariesForRecord(Model $record, int $limit = 6): Collection
    {
        // Only company/contact records own deals; anything else has none.
        if (! $record instanceof Company && ! $record instanceof Contact) {
            return new Collection;
        }

        return $record->deals()
            ->with('stage:id,name,color')
            ->orderByDesc('amount')
            ->limit($limit)
            ->get();
    }

    public function forBoardStage(int $stageId): Collection
    {
        return Deal::query()
            ->where('pipeline_stage_id', $stageId)
            ->with(['company:id,name', 'contact:id,first_name,last_name', 'owner:id,name'])
            ->orderBy('position')
            ->get();
    }
}
