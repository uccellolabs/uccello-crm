<?php

namespace Tests\Unit\Application\Teams;

use App\Application\Teams\Presenters\UserTeamPresenter;
use App\Domain\Shared\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTeamPresenterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_maps_team_and_role_fields(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['name' => 'Sales', 'slug' => 'sales']);
        $team->members()->attach($user, ['role' => TeamRole::Admin->value]);
        $user->switchTeam($team);

        $dto = (new UserTeamPresenter)->present($user->fresh(), $team);

        $this->assertSame($team->id, $dto->id);
        $this->assertSame('Sales', $dto->name);
        $this->assertSame('sales', $dto->slug);
        $this->assertFalse($dto->isPersonal);
        $this->assertSame('admin', $dto->role);
        $this->assertTrue($dto->isCurrent);
    }

    #[Test]
    public function it_allows_overriding_the_current_flag(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->personal()->create(['name' => 'Other', 'slug' => 'other']);
        $team->members()->attach($user, ['role' => TeamRole::Member->value]);

        $dto = (new UserTeamPresenter)->present($user->fresh(), $team, isCurrent: true);

        $this->assertTrue($dto->isCurrent);
    }

    #[Test]
    public function it_returns_null_role_when_membership_is_missing(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $dto = (new UserTeamPresenter)->present($user->fresh(), $team);

        $this->assertNull($dto->role);
        $this->assertNull($dto->roleLabel);
        $this->assertFalse($dto->isCurrent);
    }
}
