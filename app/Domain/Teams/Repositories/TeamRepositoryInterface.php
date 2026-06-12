<?php

namespace App\Domain\Teams\Repositories;

use App\Domain\Shared\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;

interface TeamRepositoryInterface
{
    public function create(string $name, bool $isPersonal = false): Team;

    public function updateName(Team $team, string $name): Team;

    public function delete(Team $team): void;

    public function addMember(Team $team, User $user, TeamRole $role): void;

    public function deleteInvitations(Team $team): void;

    public function deleteMemberships(Team $team): void;

    public function createInvitation(
        Team $team,
        string $email,
        TeamRole $role,
        int $invitedBy,
        \DateTimeInterface $expiresAt,
    ): TeamInvitation;

    public function deleteInvitation(TeamInvitation $invitation): void;

    public function acceptInvitation(TeamInvitation $invitation, User $user): void;

    public function updateMemberRole(Team $team, User $member, TeamRole $role): void;

    public function removeMember(Team $team, User $member): void;

    public function lockForUpdate(Team $team): Team;

    public function reassignUsersAwayFrom(Team $team, User $except): void;

    public function purge(Team $team, User $except): void;

    public function createForUser(User $user, string $name, bool $isPersonal): Team;

    public function updateNameLocked(Team $team, string $name): Team;

    public function acceptInvitationAndSwitch(User $user, TeamInvitation $invitation): void;

    public function emailIsMember(Team $team, string $email): bool;

    public function hasPendingInvitationForEmail(Team $team, string $email): bool;
}
