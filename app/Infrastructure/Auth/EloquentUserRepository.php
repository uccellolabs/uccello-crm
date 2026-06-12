<?php

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Models\User;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function create(string $name, string $email, string $password, string $locale): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'locale' => $locale,
        ]);
    }
}
