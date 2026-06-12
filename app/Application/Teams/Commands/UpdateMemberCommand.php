<?php

namespace App\Application\Teams\Commands;

use App\Domain\Shared\Enums\TeamRole;

final readonly class UpdateMemberCommand
{
    public function __construct(
        public TeamRole $role,
    ) {}
}
