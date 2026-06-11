<?php

namespace Tests\Feature\Crm;

use App\Domain\Shared\Enums\TeamRole;
use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a company belonging to the given team (bypassing the request scope).
     */
    protected function companyForTeam(Team $team, array $attributes = []): Company
    {
        $company = new Company;
        $company->forceFill([
            'name' => 'Acme',
            ...$attributes,
            'team_id' => $team->id,
        ]);
        $company->save();

        return $company;
    }

    public function test_members_can_list_companies(): void
    {
        $user = User::factory()->create();
        $this->companyForTeam($user->currentTeam, ['name' => 'Globex']);

        $this->actingAs($user)
            ->get(route('companies.index', ['current_team' => $user->currentTeam->slug]))
            ->assertOk();
    }

    public function test_a_company_can_be_created_and_is_scoped_to_the_team(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('companies.store', ['current_team' => $user->currentTeam->slug]), [
                'name' => 'Initech',
                'industry' => 'SaaS',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('companies', [
            'name' => 'Initech',
            'team_id' => $user->currentTeam->id,
        ]);
    }

    public function test_a_company_can_be_updated_and_deleted(): void
    {
        $user = User::factory()->create();
        $company = $this->companyForTeam($user->currentTeam);

        $this->actingAs($user)
            ->put(route('companies.update', ['current_team' => $user->currentTeam->slug, 'company' => $company->id]), [
                'name' => 'Acme Renamed',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'Acme Renamed']);

        $this->actingAs($user)
            ->delete(route('companies.destroy', ['current_team' => $user->currentTeam->slug, 'company' => $company->id]))
            ->assertRedirect();

        $this->assertSoftDeleted('companies', ['id' => $company->id]);
    }

    public function test_a_user_cannot_view_another_teams_company(): void
    {
        $owner = User::factory()->create();
        $company = $this->companyForTeam($owner->currentTeam);

        $intruder = User::factory()->create();

        // The intruder uses their OWN team slug but targets the other team's id.
        $this->actingAs($intruder)
            ->get(route('companies.show', ['current_team' => $intruder->currentTeam->slug, 'company' => $company->id]))
            ->assertNotFound();
    }

    public function test_owner_id_must_belong_to_the_current_team(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create(); // member of a different team

        $this->actingAs($user)
            ->post(route('companies.store', ['current_team' => $user->currentTeam->slug]), [
                'name' => 'Initech',
                'owner_id' => $stranger->id,
            ])
            ->assertSessionHasErrors('owner_id');

        $this->assertDatabaseMissing('companies', ['name' => 'Initech']);
    }

    public function test_owner_id_of_a_team_member_is_accepted(): void
    {
        $user = User::factory()->create();
        $colleague = User::factory()->create();
        $user->currentTeam->members()->attach($colleague, ['role' => TeamRole::Member->value]);

        $this->actingAs($user)
            ->post(route('companies.store', ['current_team' => $user->currentTeam->slug]), [
                'name' => 'Initech',
                'owner_id' => $colleague->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('companies', [
            'name' => 'Initech',
            'owner_id' => $colleague->id,
        ]);
    }
}
