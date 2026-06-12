<?php

namespace App\Infrastructure\Authorization;

use App\Application\Shared\Ports\AuthorizationCheckerInterface;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class GateAuthorizationChecker implements AuthorizationCheckerInterface
{
    public function can(User $user, string $ability, object|string $target): bool
    {
        return Gate::forUser($user)->allows($ability, $target);
    }
}
