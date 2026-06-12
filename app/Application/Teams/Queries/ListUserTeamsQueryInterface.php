<?php

namespace App\Application\Teams\Queries;

use App\Application\Teams\DTOs\UserTeam;
use App\Models\User;
use Illuminate\Support\Collection;

interface ListUserTeamsQueryInterface
{
    /**
     * @return Collection<int, UserTeam>
     */
    public function forUser(User $user, bool $includeCurrent = true): Collection;
}
