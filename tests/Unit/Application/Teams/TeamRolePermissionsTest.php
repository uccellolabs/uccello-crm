<?php

namespace Tests\Unit\Application\Teams;

use App\Application\Shared\Presenters\EnumLabels;
use App\Domain\Shared\Enums\TeamPermission;
use App\Domain\Shared\Enums\TeamRole;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TeamRolePermissionsTest extends TestCase
{
    #[Test]
    public function owner_has_every_permission(): void
    {
        foreach (TeamPermission::cases() as $permission) {
            $this->assertTrue(TeamRole::Owner->hasPermission($permission));
        }
    }

    #[Test]
    public function admin_can_update_team_and_manage_crm(): void
    {
        $this->assertTrue(TeamRole::Admin->hasPermission(TeamPermission::UpdateTeam));
        $this->assertTrue(TeamRole::Admin->hasPermission(TeamPermission::ManageCrmRecords));
        $this->assertTrue(TeamRole::Admin->hasPermission(TeamPermission::ManageCustomFields));
    }

    #[Test]
    public function admin_cannot_delete_team_or_remove_members(): void
    {
        $this->assertFalse(TeamRole::Admin->hasPermission(TeamPermission::DeleteTeam));
        $this->assertFalse(TeamRole::Admin->hasPermission(TeamPermission::RemoveMember));
    }

    #[Test]
    public function member_can_view_and_manage_crm_records(): void
    {
        $this->assertTrue(TeamRole::Member->hasPermission(TeamPermission::ViewCrm));
        $this->assertTrue(TeamRole::Member->hasPermission(TeamPermission::ManageCrmRecords));
    }

    #[Test]
    public function member_cannot_manage_team_settings(): void
    {
        $this->assertFalse(TeamRole::Member->hasPermission(TeamPermission::UpdateTeam));
        $this->assertFalse(TeamRole::Member->hasPermission(TeamPermission::CreateInvitation));
    }

    #[Test]
    public function owner_outranks_admin_in_hierarchy(): void
    {
        $this->assertTrue(TeamRole::Owner->isAtLeast(TeamRole::Admin));
        $this->assertFalse(TeamRole::Member->isAtLeast(TeamRole::Admin));
    }

    #[Test]
    public function assignable_roles_exclude_owner(): void
    {
        $values = array_column(EnumLabels::assignableTeamRoles(), 'value');

        $this->assertNotContains(TeamRole::Owner->value, $values);
        $this->assertContains(TeamRole::Admin->value, $values);
        $this->assertContains(TeamRole::Member->value, $values);
    }
}
