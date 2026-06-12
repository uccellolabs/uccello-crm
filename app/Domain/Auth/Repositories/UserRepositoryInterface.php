<?php

namespace App\Domain\Auth\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function create(string $name, string $email, string $password, string $locale): User;
}
