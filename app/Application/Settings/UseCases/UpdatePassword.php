<?php

namespace App\Application\Settings\UseCases;

use App\Application\Settings\Commands\UpdatePasswordCommand;
use App\Models\User;

class UpdatePassword
{
    public function handle(User $user, UpdatePasswordCommand $command): void
    {
        $user->update([
            'password' => $command->password,
        ]);
    }
}
