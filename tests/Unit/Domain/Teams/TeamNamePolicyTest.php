<?php

namespace Tests\Unit\Domain\Teams;

use App\Domain\Shared\Enums\TeamRole;
use App\Domain\Teams\Services\TeamNamePolicy;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TeamNamePolicyTest extends TestCase
{
    #[Test]
    public function it_rejects_statically_reserved_names(): void
    {
        $this->assertTrue(TeamNamePolicy::isStaticallyReserved('admin'));
        $this->assertTrue(TeamNamePolicy::isReserved('Teams', []));
    }

    #[Test]
    public function it_rejects_additional_reserved_names(): void
    {
        $this->assertTrue(TeamNamePolicy::isReserved('custom-slug', ['custom-slug']));
    }

    #[Test]
    public function it_allows_regular_team_names(): void
    {
        $this->assertFalse(TeamNamePolicy::isReserved('Acme Sales', []));
    }

    #[Test]
    public function assignable_roles_exclude_owner(): void
    {
        $assignable = TeamRole::assignable();

        $this->assertContains(TeamRole::Admin, $assignable);
        $this->assertContains(TeamRole::Member, $assignable);
        $this->assertNotContains(TeamRole::Owner, $assignable);
    }
}
