<?php

namespace App\Application\Settings\UseCases;

use App\Application\Settings\Commands\UpdateLocaleCommand;
use App\Models\User;

class UpdateLocale
{
    public function handle(?User $user, UpdateLocaleCommand $command): void
    {
        if ($user) {
            $user->update(['locale' => $command->locale]);
        }
    }
}
