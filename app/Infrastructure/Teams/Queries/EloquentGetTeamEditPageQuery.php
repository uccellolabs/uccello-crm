<?php

namespace App\Infrastructure\Teams\Queries;

use App\Application\Shared\Presenters\EnumLabels;
use App\Application\Teams\DTOs\TeamEditPageData;
use App\Application\Teams\Presenters\TeamPermissionsPresenter;
use App\Application\Teams\Queries\GetTeamEditPageQueryInterface;
use App\Domain\Shared\Enums\TeamPermission;
use App\Models\Membership;
use App\Models\Team;
use App\Models\User;

class EloquentGetTeamEditPageQuery implements GetTeamEditPageQueryInterface
{
    public function __construct(
        private readonly TeamPermissionsPresenter $teamPermissionsPresenter,
    ) {}

    public function forUserAndTeam(User $user, Team $team): TeamEditPageData
    {
        return new TeamEditPageData(
            team: [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'isPersonal' => $team->is_personal,
            ],
            members: array_values($team->members()->get()->map(function (User $member) {
                /** @var Membership $membership */
                $membership = $member->getRelation('pivot');

                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'avatar' => $member->avatar ?? null,
                    'role' => $membership->role->value,
                    'role_label' => EnumLabels::teamRole($membership->role),
                ];
            })->all()),
            invitations: $this->pendingInvitationsFor($user, $team),
            permissions: $this->teamPermissionsPresenter->present($user, $team),
            availableRoles: EnumLabels::assignableTeamRoles(),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function pendingInvitationsFor(User $user, Team $team): array
    {
        $canManageInvitations = $user->hasTeamPermission($team, TeamPermission::CreateInvitation)
            || $user->hasTeamPermission($team, TeamPermission::CancelInvitation);

        return array_values($team->invitations()
            ->whereNull('accepted_at')
            ->get()
            ->map(function ($invitation) use ($canManageInvitations) {
                $data = [
                    'email' => $invitation->email,
                    'role' => $invitation->role->value,
                    'role_label' => EnumLabels::teamRole($invitation->role),
                    'created_at' => $invitation->created_at->toISOString(),
                ];

                if ($canManageInvitations) {
                    $data['code'] = $invitation->code;
                }

                return $data;
            })
            ->all());
    }
}
