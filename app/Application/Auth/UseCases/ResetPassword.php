<?php

namespace App\Application\Auth\UseCases;

use App\Application\Auth\Commands\ResetPasswordCommand;
use App\Models\User;

class ResetPassword
{
    public function handle(User $user, ResetPasswordCommand $command): void
    {
        $user->update([
            'password' => $command->password,
        ]);
    }
}
