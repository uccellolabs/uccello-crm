<?php

namespace App\Application\Auth\Commands;

final readonly class RegisterUserCommand
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $locale,
    ) {}
}
