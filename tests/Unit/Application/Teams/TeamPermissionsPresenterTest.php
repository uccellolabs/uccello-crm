<?php

namespace Tests\Unit\Application\Teams;

use App\Application\Teams\Presenters\TeamPermissionsPresenter;
use App\Domain\Shared\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TeamPermissionsPresenterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function owners_receive_all_team_permissions(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $permissions = (new TeamPermissionsPresenter)->present($user->fresh(), $team);

        $this->assertTrue($permissions->canUpdateTeam);
        $this->assertTrue($permissions->canDeleteTeam);
        $this->assertTrue($permissions->canAddMember);
        $this->assertTrue($permissions->canCreateInvitation);
    }

    #[Test]
    public function admins_can_manage_invitations_but_not_delete_the_team(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user, ['role' => TeamRole::Admin->value]);

        $permissions = (new TeamPermissionsPresenter)->present($user->fresh(), $team);

        $this->assertTrue($permissions->canUpdateTeam);
        $this->assertFalse($permissions->canDeleteTeam);
        $this->assertTrue($permissions->canCreateInvitation);
        $this->assertTrue($permissions->canCancelInvitation);
    }

    #[Test]
    public function members_have_no_team_management_permissions(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user, ['role' => TeamRole::Member->value]);

        $permissions = (new TeamPermissionsPresenter)->present($user->fresh(), $team);

        $this->assertFalse($permissions->canUpdateTeam);
        $this->assertFalse($permissions->canDeleteTeam);
        $this->assertFalse($permissions->canAddMember);
        $this->assertFalse($permissions->canCreateInvitation);
    }

    #[Test]
    public function users_without_membership_receive_no_permissions(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $permissions = (new TeamPermissionsPresenter)->present($user->fresh(), $team);

        $this->assertFalse($permissions->canUpdateTeam);
        $this->assertFalse($permissions->canRemoveMember);
    }
}
