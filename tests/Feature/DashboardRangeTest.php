<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardRangeTest extends TestCase
{
    use RefreshDatabase;

    protected function companyAt(Team $team, string $createdAt): void
    {
        $company = new Company;
        $company->forceFill(['name' => 'Acme', 'team_id' => $team->id, 'created_at' => $createdAt]);
        $company->save();
    }

    public function test_kpis_respect_the_selected_date_range(): void
    {
        $user = User::factory()->create();
        $team = $user->currentTeam;

        // One company inside the window, one well outside it.
        $this->companyAt($team, now()->subDays(3)->toDateTimeString());
        $this->companyAt($team, now()->subDays(100)->toDateTimeString());

        $this->actingAs($user)
            ->get(route('dashboard', [
                'current_team' => $team->slug,
                'from' => now()->subDays(7)->toDateString(),
                'to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('kpis.new_companies.value', 1)
                ->where('range.from', now()->subDays(7)->toDateString())
            );
    }

    public function test_dashboard_data_is_team_scoped(): void
    {
        $owner = User::factory()->create();
        $this->companyAt($owner->currentTeam, now()->subDay()->toDateTimeString());

        $other = User::factory()->create();

        $this->actingAs($other)
            ->get(route('dashboard', ['current_team' => $other->currentTeam->slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('kpis.new_companies.value', 0)
            );
    }
}
