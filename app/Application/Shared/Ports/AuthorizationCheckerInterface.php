<?php

namespace App\Application\Shared\Ports;

use App\Models\User;

interface AuthorizationCheckerInterface
{
    public function can(User $user, string $ability, object|string $target): bool;
}
