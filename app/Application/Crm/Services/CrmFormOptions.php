<?php

namespace App\Application\Crm\Services;

use App\Application\Crm\DTOs\SelectOptionData;
use App\Models\User;

/**
 * Port for the select-option lists used by CRM forms (owners, companies,
 * contacts, pipelines). Implemented in the infrastructure layer (Eloquent).
 */
interface CrmFormOptions
{
    /**
     * The given user's current-team members as select options.
     *
     * @return list<SelectOptionData>
     */
    public function owners(User $user): array;

    /**
     * @return list<SelectOptionData>
     */
    public function companies(): array;

    /**
     * @return list<SelectOptionData>
     */
    public function contacts(): array;

    /**
     * @return list<SelectOptionData>
     */
    public function deals(): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function pipelinesWithStages(): array;
}
