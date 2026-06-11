<?php

namespace App\Infrastructure\Persistence\Eloquent\Queries;

use App\Application\Deals\DTOs\DealStatsData;
use App\Application\Deals\Queries\GetDealStatsForContactQueryInterface;
use App\Domain\Shared\Enums\DealStatus;
use App\Models\Contact;

class EloquentGetDealStatsForContactQuery implements GetDealStatsForContactQueryInterface
{
    public function statsForContact(Contact $contact): DealStatsData
    {
        return new DealStatsData(
            openDeals: $contact->deals()->where('status', DealStatus::Open)->count(),
            pipelineValue: (float) $contact->deals()->where('status', DealStatus::Open)->sum('amount'),
            wonValue: (float) $contact->deals()->where('status', DealStatus::Won)->sum('amount'),
            deals: $contact->deals()->count(),
        );
    }
}
