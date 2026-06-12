<?php

namespace App\Application\Teams\DTOs;

final readonly class TeamEditPageData
{
    /**
     * @param  array<string, mixed>  $team
     * @param  list<array<string, mixed>>  $members
     * @param  list<array<string, mixed>>  $invitations
     * @param  list<array<string, mixed>>  $availableRoles
     */
    public function __construct(
        public array $team,
        public array $members,
        public array $invitations,
        public TeamPermissions $permissions,
        public array $availableRoles,
    ) {}
}
