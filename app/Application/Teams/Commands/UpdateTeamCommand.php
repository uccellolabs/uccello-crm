<?php

namespace App\Application\Teams\Commands;

final readonly class UpdateTeamCommand
{
    public function __construct(
        public string $name,
    ) {}
}
