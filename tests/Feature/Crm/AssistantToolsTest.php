<?php

namespace Tests\Feature\Crm;

use App\Application\Pipelines\UseCases\CreateDefaultPipeline;
use App\Domain\Shared\Enums\CustomFieldType;
use App\Infrastructure\Assistant\AssistantTools;
use App\Models\Company;
use App\Models\CustomFieldDefinition;
use App\Models\Deal;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssistantToolsTest extends TestCase
{
    use RefreshDatabase;

    private function tools(): AssistantTools
    {
        return app(AssistantTools::class);
    }

    public function test_search_returns_records_with_resolved_custom_fields(): void
    {
        $user = User::factory()->create();
        $team = $user->currentTeam;

        $this->segmentDefinition($team);

        $company = new Company;
        $company->forceFill([
            'team_id' => $team->id,
            'name' => 'Acme Corp',
            'city' => 'Lyon',
            'custom_fields' => ['segment' => 'smb'],
        ])->save();

        $this->actingAs($user);

        $result = $this->tools()->execute('search_records', ['module' => 'company', 'query' => 'Acme']);

        $this->assertFalse($result['error']);
        $this->assertSame(1, $result['data']['count']);

        $record = $result['data']['records'][0];
        $this->assertSame('Acme Corp', $record['name']);
        $this->assertSame(['label' => 'Segment', 'value' => 'PME'], $record['custom_fields']['segment']);
    }

    public function test_search_can_filter_by_custom_field_key(): void
    {
        $user = User::factory()->create();
        $team = $user->currentTeam;
        $this->segmentDefinition($team);

        $this->company($team, 'Big Co', ['segment' => 'ent']);
        $this->company($team, 'Small Co', ['segment' => 'smb']);

        $this->actingAs($user);

        $result = $this->tools()->execute('search_records', [
            'module' => 'company',
            'filters' => ['segment' => 'ent'],
        ]);

        $this->assertSame(1, $result['data']['count']);
        $this->assertSame('Big Co', $result['data']['records'][0]['name']);
    }

    public function test_aggregate_sums_and_groups_deals(): void
    {
        $user = User::factory()->create();
        $team = $user->currentTeam;
        $pipeline = app(CreateDefaultPipeline::class)->handle($team);
        $lead = $pipeline->stages()->where('key', 'lead')->firstOrFail();
        $won = $pipeline->stages()->where('key', 'won')->firstOrFail();

        $this->deal($team, $pipeline->id, $lead->id, 'Deal A', 1000, 'open');
        $this->deal($team, $pipeline->id, $lead->id, 'Deal B', 2000, 'open');
        $this->deal($team, $pipeline->id, $won->id, 'Deal C', 5000, 'won');

        $this->actingAs($user);

        $sum = $this->tools()->execute('aggregate_records', ['module' => 'deal', 'operation' => 'sum', 'field' => 'amount']);
        $this->assertSame(8000.0, (float) $sum['data']['value']);

        $count = $this->tools()->execute('aggregate_records', ['module' => 'deal', 'operation' => 'count']);
        $this->assertSame(3, $count['data']['value']);

        $grouped = $this->tools()->execute('aggregate_records', [
            'module' => 'deal',
            'operation' => 'sum',
            'field' => 'amount',
            'group_by' => 'status',
        ]);
        $byGroup = collect($grouped['data']['groups'])->keyBy('group');
        $this->assertEqualsWithDelta(3000.0, (float) $byGroup['En cours']['value'], 0.01);
        $this->assertEqualsWithDelta(5000.0, (float) $byGroup['Gagnée']['value'], 0.01);
    }

    public function test_get_record_includes_related_data(): void
    {
        $user = User::factory()->create();
        $team = $user->currentTeam;
        $pipeline = app(CreateDefaultPipeline::class)->handle($team);
        $lead = $pipeline->stages()->where('key', 'lead')->firstOrFail();

        $company = $this->company($team, 'Globex', []);
        $this->deal($team, $pipeline->id, $lead->id, 'Globex deal', 4200, 'open', $company->id);

        $this->actingAs($user);

        $result = $this->tools()->execute('get_record', ['module' => 'company', 'id' => $company->id]);

        $this->assertFalse($result['error']);
        $this->assertSame('Globex', $result['data']['record']['name']);
        $this->assertCount(1, $result['data']['record']['related']['deals']);
        $this->assertSame('Globex deal', $result['data']['record']['related']['deals'][0]['name']);
    }

    public function test_search_is_scoped_to_the_current_team(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->company($user->currentTeam, 'Mine SARL', []);
        $this->company($other->currentTeam, 'Theirs SARL', []);

        $this->actingAs($user);

        $result = $this->tools()->execute('search_records', ['module' => 'company']);

        $names = collect($result['data']['records'])->pluck('name');
        $this->assertTrue($names->contains('Mine SARL'));
        $this->assertFalse($names->contains('Theirs SARL'));
    }

    private function segmentDefinition(Team $team): void
    {
        $definition = new CustomFieldDefinition;
        $definition->forceFill([
            'team_id' => $team->id,
            'entity_type' => 'company',
            'key' => 'segment',
            'label' => 'Segment',
            'type' => CustomFieldType::Select,
            'options' => ['choices' => [
                ['value' => 'smb', 'label' => 'PME'],
                ['value' => 'ent', 'label' => 'Grand compte'],
            ]],
            'is_required' => false,
            'is_filterable' => true,
            'position' => 0,
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $customFields
     */
    private function company(Team $team, string $name, array $customFields): Company
    {
        $company = new Company;
        $company->forceFill([
            'team_id' => $team->id,
            'name' => $name,
            'custom_fields' => $customFields,
        ])->save();

        return $company;
    }

    private function deal(Team $team, int $pipelineId, int $stageId, string $name, int $amount, string $status, ?int $companyId = null): void
    {
        $deal = new Deal;
        $deal->forceFill([
            'team_id' => $team->id,
            'pipeline_id' => $pipelineId,
            'pipeline_stage_id' => $stageId,
            'company_id' => $companyId,
            'name' => $name,
            'amount' => $amount,
            'currency' => 'EUR',
            'status' => $status,
            'position' => 0,
        ])->save();
    }
}
