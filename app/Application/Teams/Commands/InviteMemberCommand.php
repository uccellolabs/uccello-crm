<?php

namespace App\Application\Teams\Commands;

use App\Domain\Shared\Enums\TeamRole;

final readonly class InviteMemberCommand
{
    public function __construct(
        public string $email,
        public TeamRole $role,
    ) {}
}
