<?php

namespace Tests\Feature\Crm;

use App\Application\Pipelines\UseCases\CreateDefaultPipeline;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed a default pipeline for the team (without request scope).
     */
    protected function pipelineFor(Team $team): Pipeline
    {
        return app(CreateDefaultPipeline::class)->handle($team);
    }

    public function test_visiting_the_board_creates_a_default_pipeline(): void
    {
        $user = User::factory()->create();

        $this->assertSame(0, Pipeline::withoutGlobalScope('team')->count());

        $this->actingAs($user)
            ->get(route('deals.board', ['current_team' => $user->currentTeam->slug]))
            ->assertOk();

        $this->assertSame(1, Pipeline::withoutGlobalScope('team')->where('team_id', $user->currentTeam->id)->count());
    }

    public function test_a_deal_can_be_created_in_a_stage(): void
    {
        $user = User::factory()->create();
        $pipeline = $this->pipelineFor($user->currentTeam);
        $lead = $pipeline->stages()->where('key', 'lead')->first();

        $this->actingAs($user)
            ->post(route('deals.store', ['current_team' => $user->currentTeam->slug]), [
                'name' => 'Gros contrat',
                'amount' => 25000,
                'pipeline_id' => $pipeline->id,
                'pipeline_stage_id' => $lead->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('deals', [
            'name' => 'Gros contrat',
            'pipeline_stage_id' => $lead->id,
            'status' => 'open',
            'team_id' => $user->currentTeam->id,
        ]);
    }

    public function test_moving_a_deal_to_a_won_stage_marks_it_won(): void
    {
        $user = User::factory()->create();
        $pipeline = $this->pipelineFor($user->currentTeam);
        $lead = $pipeline->stages()->where('key', 'lead')->first();
        $won = $pipeline->stages()->where('key', 'won')->first();

        $deal = new Deal;
        $deal->forceFill([
            'team_id' => $user->currentTeam->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $lead->id,
            'name' => 'Affaire',
            'status' => 'open',
            'position' => 0,
        ]);
        $deal->save();

        $this->actingAs($user)
            ->patch(route('deals.move', ['current_team' => $user->currentTeam->slug, 'deal' => $deal->id]), [
                'stage_id' => $won->id,
                'position' => 0,
            ])
            ->assertRedirect();

        $deal->refresh();
        $this->assertSame('won', $deal->status->value);
        $this->assertNotNull($deal->closed_at);
        $this->assertSame($won->id, $deal->pipeline_stage_id);
    }

    public function test_a_deal_cannot_move_to_a_stage_of_another_pipeline(): void
    {
        $user = User::factory()->create();
        $pipelineA = $this->pipelineFor($user->currentTeam);
        $pipelineB = $this->pipelineFor($user->currentTeam);

        $leadA = $pipelineA->stages()->where('key', 'lead')->first();
        $wonB = $pipelineB->stages()->where('key', 'won')->first();

        $deal = new Deal;
        $deal->forceFill([
            'team_id' => $user->currentTeam->id,
            'pipeline_id' => $pipelineA->id,
            'pipeline_stage_id' => $leadA->id,
            'name' => 'Affaire',
            'status' => 'open',
            'position' => 0,
        ]);
        $deal->save();

        $this->actingAs($user)
            ->patch(route('deals.move', ['current_team' => $user->currentTeam->slug, 'deal' => $deal->id]), [
                'stage_id' => $wonB->id,
                'position' => 0,
            ])
            ->assertSessionHasErrors('stage_id');
    }

    public function test_a_user_cannot_view_another_teams_deal(): void
    {
        $owner = User::factory()->create();
        $pipeline = $this->pipelineFor($owner->currentTeam);
        $lead = $pipeline->stages()->where('key', 'lead')->first();

        $deal = new Deal;
        $deal->forceFill([
            'team_id' => $owner->currentTeam->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $lead->id,
            'name' => 'Secret',
            'status' => 'open',
            'position' => 0,
        ]);
        $deal->save();

        $intruder = User::factory()->create();

        $this->actingAs($intruder)
            ->get(route('deals.show', ['current_team' => $intruder->currentTeam->slug, 'deal' => $deal->id]))
            ->assertNotFound();
    }
}
