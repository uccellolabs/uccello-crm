<?php

namespace App\Application\Settings\Commands;

final readonly class UpdatePasswordCommand
{
    public function __construct(
        public string $password,
    ) {}
}
