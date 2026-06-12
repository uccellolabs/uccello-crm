<?php

namespace App\Application\Teams\Commands;

final readonly class CreateTeamCommand
{
    public function __construct(
        public string $name,
        public bool $isPersonal = false,
    ) {}
}
