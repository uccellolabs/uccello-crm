<?php

namespace App\Application\Deals\Queries;

use App\Application\Deals\DTOs\DealStatsData;
use App\Models\Contact;

interface GetDealStatsForContactQueryInterface
{
    public function statsForContact(Contact $contact): DealStatsData;
}
