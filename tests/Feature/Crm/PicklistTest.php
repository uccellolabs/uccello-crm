<?php

namespace Tests\Feature\Crm;

use App\Domain\Shared\Enums\TeamRole;
use App\Models\Company;
use App\Models\PicklistOption;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PicklistTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_admin_screen_seeds_the_default_options(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('picklists.index', ['current_team' => $user->currentTeam->slug]))
            ->assertOk();

        $this->assertDatabaseHas('picklist_options', [
            'team_id' => $user->currentTeam->id,
            'picklist' => 'activity_type',
            'value' => 'call',
            'is_system' => true,
        ]);
        $this->assertDatabaseHas('picklist_options', [
            'team_id' => $user->currentTeam->id,
            'picklist' => 'industry',
            'value' => 'SaaS',
        ]);
    }

    public function test_an_admin_can_add_an_option(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('picklists.store', ['current_team' => $user->currentTeam->slug]), [
                'picklist' => 'activity_type',
                'label' => 'Visio',
                'color' => '#06b6d4',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('picklist_options', [
            'team_id' => $user->currentTeam->id,
            'picklist' => 'activity_type',
            'value' => 'visio',
            'label' => 'Visio',
            'is_system' => false,
        ]);
    }

    public function test_a_system_option_cannot_be_deleted(): void
    {
        $user = User::factory()->create();

        // Seed via the admin screen, then target a system option.
        $this->actingAs($user)
            ->get(route('picklists.index', ['current_team' => $user->currentTeam->slug]));

        $option = PicklistOption::query()
            ->where('picklist', 'task_priority')
            ->where('value', 'normal')
            ->sole();

        $this->actingAs($user)
            ->delete(route('picklists.destroy', [
                'current_team' => $user->currentTeam->slug,
                'picklistOption' => $option->id,
            ]))
            ->assertForbidden();
    }

    public function test_a_member_cannot_manage_picklists(): void
    {
        $owner = User::factory()->create();
        $team = $owner->currentTeam;

        $member = User::factory()->create();
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);
        $member->switchTeam($team);

        $this->actingAs($member)
            ->get(route('picklists.index', ['current_team' => $team->slug]))
            ->assertForbidden();
    }

    public function test_an_activity_accepts_an_admin_added_type(): void
    {
        $user = User::factory()->create();
        $slug = $user->currentTeam->slug;

        $company = new Company;
        $company->forceFill(['name' => 'Acme', 'team_id' => $user->currentTeam->id]);
        $company->save();

        $this->actingAs($user)
            ->post(route('picklists.store', ['current_team' => $slug]), [
                'picklist' => 'activity_type',
                'label' => 'Visio',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('activities.store', ['current_team' => $slug]), [
                'type' => 'visio',
                'subject' => 'Point hebdo',
                'subjectable_type' => 'company',
                'subjectable_id' => $company->id,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('activities', [
            'team_id' => $user->currentTeam->id,
            'type' => 'visio',
        ]);
    }

    public function test_an_unknown_task_priority_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('tasks.store', ['current_team' => $user->currentTeam->slug]), [
                'title' => 'Tâche',
                'priority' => 'urgentissime',
            ])
            ->assertSessionHasErrors('priority');
    }

    public function test_a_pipeline_stage_can_be_renamed_and_an_empty_one_deleted(): void
    {
        $user = User::factory()->create();
        $slug = $user->currentTeam->slug;

        // Lazily creates the default pipeline.
        $this->actingAs($user)
            ->get(route('pipeline-settings.index', ['current_team' => $slug]))
            ->assertOk();

        $stage = PipelineStage::query()->where('key', 'qualified')->sole();

        $this->actingAs($user)
            ->patch(route('pipeline-settings.stages.update', [
                'current_team' => $slug,
                'stage' => $stage->id,
            ]), ['name' => 'Découverte', 'color' => '#06b6d4', 'probability' => 25])
            ->assertRedirect();

        $this->assertSame('Découverte', $stage->fresh()->name);

        $this->actingAs($user)
            ->delete(route('pipeline-settings.stages.destroy', [
                'current_team' => $slug,
                'stage' => $stage->id,
            ]))
            ->assertRedirect();

        $this->assertDatabaseMissing('pipeline_stages', ['id' => $stage->id]);
    }

    public function test_a_terminal_stage_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $slug = $user->currentTeam->slug;

        $this->actingAs($user)
            ->get(route('pipeline-settings.index', ['current_team' => $slug]));

        $won = PipelineStage::query()->where('is_won', true)->sole();

        $this->actingAs($user)
            ->delete(route('pipeline-settings.stages.destroy', [
                'current_team' => $slug,
                'stage' => $won->id,
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('pipeline_stages', ['id' => $won->id]);
    }
}
