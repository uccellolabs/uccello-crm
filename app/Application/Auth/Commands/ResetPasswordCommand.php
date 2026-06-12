<?php

namespace App\Application\Auth\Commands;

final readonly class ResetPasswordCommand
{
    public function __construct(
        public string $password,
    ) {}
}
