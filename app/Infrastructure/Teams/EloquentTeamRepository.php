<?php

namespace App\Infrastructure\Teams;

use App\Domain\Shared\Enums\TeamRole;
use App\Domain\Teams\Repositories\TeamRepositoryInterface;
use App\Infrastructure\Teams\Notifications\TeamInvitation as TeamInvitationNotification;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class EloquentTeamRepository implements TeamRepositoryInterface
{
    public function create(string $name, bool $isPersonal = false): Team
    {
        return Team::create([
            'name' => $name,
            'is_personal' => $isPersonal,
        ]);
    }

    public function updateName(Team $team, string $name): Team
    {
        $team->update(['name' => $name]);

        return $team->fresh();
    }

    public function delete(Team $team): void
    {
        $team->delete();
    }

    public function addMember(Team $team, User $user, TeamRole $role): void
    {
        $team->memberships()->create([
            'user_id' => $user->id,
            'role' => $role,
        ]);
    }

    public function deleteInvitations(Team $team): void
    {
        $team->invitations()->delete();
    }

    public function deleteMemberships(Team $team): void
    {
        $team->memberships()->delete();
    }

    public function createInvitation(
        Team $team,
        string $email,
        TeamRole $role,
        int $invitedBy,
        \DateTimeInterface $expiresAt,
    ): TeamInvitation {
        $invitation = $team->invitations()->create([
            'email' => $email,
            'role' => $role,
            'invited_by' => $invitedBy,
            'expires_at' => $expiresAt,
        ]);

        Notification::route('mail', $invitation->email)
            ->notify(new TeamInvitationNotification($invitation));

        return $invitation;
    }

    public function deleteInvitation(TeamInvitation $invitation): void
    {
        $invitation->delete();
    }

    public function acceptInvitation(TeamInvitation $invitation, User $user): void
    {
        $team = $invitation->team;

        $team->memberships()->firstOrCreate(
            ['user_id' => $user->id],
            ['role' => $invitation->role],
        );

        $invitation->update(['accepted_at' => now()]);
    }

    public function updateMemberRole(Team $team, User $member, TeamRole $role): void
    {
        $team->memberships()
            ->where('user_id', $member->id)
            ->firstOrFail()
            ->update(['role' => $role]);
    }

    public function removeMember(Team $team, User $member): void
    {
        $team->memberships()
            ->where('user_id', $member->id)
            ->delete();
    }

    public function lockForUpdate(Team $team): Team
    {
        return Team::whereKey($team->id)->lockForUpdate()->firstOrFail();
    }

    public function reassignUsersAwayFrom(Team $team, User $except): void
    {
        User::where('current_team_id', $team->id)
            ->where('id', '!=', $except->id)
            ->each(fn (User $affectedUser) => $affectedUser->switchTeam($affectedUser->personalTeam()));
    }

    public function purge(Team $team, User $except): void
    {
        DB::transaction(function () use ($team, $except) {
            $this->reassignUsersAwayFrom($team, $except);
            $this->deleteInvitations($team);
            $this->deleteMemberships($team);
            $this->delete($team);
        });
    }

    public function createForUser(User $user, string $name, bool $isPersonal): Team
    {
        return DB::transaction(function () use ($user, $name, $isPersonal) {
            $team = $this->create($name, $isPersonal);

            $this->addMember($team, $user, TeamRole::Owner);

            $user->switchTeam($team);

            return $team;
        });
    }

    public function updateNameLocked(Team $team, string $name): Team
    {
        return DB::transaction(function () use ($team, $name) {
            $team = $this->lockForUpdate($team);

            return $this->updateName($team, $name);
        });
    }

    public function acceptInvitationAndSwitch(User $user, TeamInvitation $invitation): void
    {
        DB::transaction(function () use ($user, $invitation) {
            $this->acceptInvitation($invitation, $user);

            $user->switchTeam($invitation->team);
        });
    }

    public function emailIsMember(Team $team, string $email): bool
    {
        return $team->members()
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->exists();
    }

    public function hasPendingInvitationForEmail(Team $team, string $email): bool
    {
        return $team->invitations()
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->whereNull('accepted_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }
}
