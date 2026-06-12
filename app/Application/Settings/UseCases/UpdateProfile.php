<?php

namespace App\Application\Settings\UseCases;

use App\Application\Settings\Commands\UpdateProfileCommand;
use App\Models\User;

class UpdateProfile
{
    public function handle(User $user, UpdateProfileCommand $command): void
    {
        $user->fill($command->toArray());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }
}
