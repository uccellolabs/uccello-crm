<?php

namespace Tests\Feature\Crm;

use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function companyFor(Team $team, string $name = 'Acme'): Company
    {
        $company = new Company;
        $company->forceFill(['name' => $name, 'team_id' => $team->id]);
        $company->save();

        return $company;
    }

    public function test_an_activity_can_be_logged_against_a_company(): void
    {
        $user = User::factory()->create();
        $company = $this->companyFor($user->currentTeam);

        $this->actingAs($user)
            ->post(route('activities.store', ['current_team' => $user->currentTeam->slug]), [
                'type' => 'call',
                'subject' => 'Premier contact',
                'subjectable_type' => 'company',
                'subjectable_id' => $company->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('activities', [
            'subjectable_type' => 'company',
            'subjectable_id' => $company->id,
            'type' => 'call',
            'user_id' => $user->id,
            'team_id' => $user->currentTeam->id,
        ]);
    }

    public function test_an_activity_cannot_attach_to_another_teams_record(): void
    {
        $owner = User::factory()->create();
        $foreignCompany = $this->companyFor($owner->currentTeam, 'Foreign');

        $intruder = User::factory()->create();

        $this->actingAs($intruder)
            ->post(route('activities.store', ['current_team' => $intruder->currentTeam->slug]), [
                'type' => 'note',
                'subjectable_type' => 'company',
                'subjectable_id' => $foreignCompany->id,
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('activities', [
            'subjectable_id' => $foreignCompany->id,
        ]);
    }

    public function test_an_unsupported_morph_type_is_rejected_without_a_server_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('activities.store', ['current_team' => $user->currentTeam->slug]), [
                'type' => 'note',
                'subjectable_type' => 'invoice', // not a CRM morph type
                'subjectable_id' => 1,
            ])
            ->assertSessionHasErrors('subjectable_type');
    }

    public function test_a_task_can_be_attached_and_toggled(): void
    {
        $user = User::factory()->create();
        $company = $this->companyFor($user->currentTeam);

        $this->actingAs($user)
            ->post(route('tasks.store', ['current_team' => $user->currentTeam->slug]), [
                'title' => 'Rappeler le client',
                'priority' => 'high',
                'taskable_type' => 'company',
                'taskable_id' => $company->id,
            ])
            ->assertRedirect();

        $task = $company->tasks()->sole();
        $this->assertNull($task->completed_at);

        $this->actingAs($user)
            ->patch(route('tasks.toggle', ['current_team' => $user->currentTeam->slug, 'task' => $task->id]))
            ->assertRedirect();

        $this->assertNotNull($task->fresh()->completed_at);
    }

    public function test_a_task_cannot_attach_to_another_teams_record(): void
    {
        $owner = User::factory()->create();
        $foreignCompany = $this->companyFor($owner->currentTeam, 'Foreign');

        $intruder = User::factory()->create();

        $this->actingAs($intruder)
            ->post(route('tasks.store', ['current_team' => $intruder->currentTeam->slug]), [
                'title' => 'Intrusion',
                'priority' => 'normal',
                'taskable_type' => 'company',
                'taskable_id' => $foreignCompany->id,
            ])
            ->assertNotFound();
    }

    public function test_the_task_create_page_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('tasks.create', ['current_team' => $user->currentTeam->slug]))
            ->assertOk();
    }

    public function test_storing_from_the_create_page_redirects_to_the_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('tasks.store', ['current_team' => $user->currentTeam->slug]), [
                'title' => 'Tâche autonome',
                'priority' => 'normal',
                'to_index' => true,
            ])
            ->assertRedirect(route('tasks.index', ['current_team' => $user->currentTeam->slug]));

        $this->assertDatabaseHas('tasks', [
            'title' => 'Tâche autonome',
            'team_id' => $user->currentTeam->id,
        ]);
    }
}
