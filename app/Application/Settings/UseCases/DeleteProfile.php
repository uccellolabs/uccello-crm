<?php

namespace App\Application\Settings\UseCases;

use App\Models\User;

class DeleteProfile
{
    public function handle(User $user): void
    {
        $user->delete();
    }
}
