<?php

namespace Database\Seeders;

use App\Application\Pipelines\UseCases\CreateDefaultPipeline;
use App\Domain\Shared\Enums\CustomFieldType;
use App\Domain\Shared\Enums\DealStatus;
use App\Domain\Shared\Enums\TeamRole;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\CustomFieldDefinition;
use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CrmDemoSeeder extends Seeder
{
    /**
     * Seed a realistic, single-team CRM dataset for first-run demos.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Démo Uccello',
            'email' => 'demo@uccello.test',
        ]);

        $team = Team::factory()->create(['name' => 'Acme CRM', 'slug' => 'acme-crm']);
        $team->memberships()->create(['user_id' => $user->id, 'role' => TeamRole::Owner]);
        $user->switchTeam($team);

        // A few teammates so owner avatars are varied across the workspace.
        $owners = collect([$user->id]);
        foreach (['Camille Roy', 'Karim Benali', 'Lucie Marchand', 'Tom Lefèvre'] as $name) {
            $member = User::factory()->create(['name' => $name]);
            $team->memberships()->create(['user_id' => $member->id, 'role' => TeamRole::Member]);
            $owners->push($member->id);
        }

        $pipeline = app(CreateDefaultPipeline::class)->handle($team);
        $stages = $pipeline->stages()->get()->keyBy('key');

        $this->customFields($team);

        $companies = Company::factory()->count(15)->create(['team_id' => $team->id])
            ->each(function (Company $company) use ($owners) {
                $company->owner_id = $owners->random();
                $company->custom_fields = [
                    'segment' => fake()->randomElement(['PME', 'ETI', 'Grand compte']),
                    'effectif' => fake()->numberBetween(5, 5000),
                ];
                $company->created_at = Carbon::instance(fake()->dateTimeBetween('-28 days', 'now'));
                $company->saveQuietly();
            });

        $contacts = Contact::factory()->count(40)->create(['team_id' => $team->id])
            ->each(function (Contact $contact) use ($owners, $companies) {
                $contact->owner_id = $owners->random();
                $contact->company_id = $companies->random()->id;
                $contact->created_at = Carbon::instance(fake()->dateTimeBetween('-28 days', 'now'));
                $contact->saveQuietly();
            });

        $this->deals($team, $owners, $pipeline->id, $stages, $companies, $contacts);
        $this->tasksAndActivities($team, $user, $companies, $contacts);
    }

    /**
     * Define a few custom fields per module so they are visible immediately.
     */
    protected function customFields(Team $team): void
    {
        $definitions = [
            ['entity_type' => 'company', 'key' => 'segment', 'label' => 'Segment', 'type' => CustomFieldType::Select,
                'is_filterable' => true, 'options' => ['choices' => array_map(
                    fn ($v) => ['value' => $v, 'label' => $v],
                    ['PME', 'ETI', 'Grand compte'],
                )]],
            ['entity_type' => 'company', 'key' => 'effectif', 'label' => 'Effectif', 'type' => CustomFieldType::Number],
            ['entity_type' => 'contact', 'key' => 'linkedin', 'label' => 'LinkedIn', 'type' => CustomFieldType::Url],
            ['entity_type' => 'deal', 'key' => 'source', 'label' => 'Source', 'type' => CustomFieldType::Select,
                'options' => ['choices' => array_map(
                    fn ($v) => ['value' => $v, 'label' => $v],
                    ['Inbound', 'Outbound', 'Recommandation', 'Salon'],
                )]],
        ];

        foreach ($definitions as $position => $definition) {
            CustomFieldDefinition::factory()->create([
                'team_id' => $team->id,
                'position' => $position,
                ...$definition,
            ]);
        }
    }

    /**
     * Seed deals spread across the pipeline, including some won and lost.
     *
     * @param  Collection<int, int>  $owners
     * @param  Collection<int, PipelineStage>  $stages
     * @param  \Illuminate\Database\Eloquent\Collection<int, Company>  $companies
     * @param  \Illuminate\Database\Eloquent\Collection<int, Contact>  $contacts
     */
    protected function deals(Team $team, Collection $owners, int $pipelineId, $stages, $companies, $contacts): void
    {
        $positions = [];

        for ($i = 0; $i < 28; $i++) {
            $stageKey = fake()->randomElement([
                'lead', 'lead', 'qualified', 'qualified', 'proposal',
                'negotiation', 'won', 'won', 'lost',
            ]);
            $stage = $stages[$stageKey];
            $positions[$stage->id] = ($positions[$stage->id] ?? -1) + 1;

            $status = match (true) {
                $stage->is_won => DealStatus::Won,
                $stage->is_lost => DealStatus::Lost,
                default => DealStatus::Open,
            };

            $deal = Deal::factory()->create([
                'team_id' => $team->id,
                'pipeline_id' => $pipelineId,
                'pipeline_stage_id' => $stage->id,
                'company_id' => $companies->random()->id,
                'contact_id' => $contacts->random()->id,
                'owner_id' => $owners->random(),
                'status' => $status,
                'position' => $positions[$stage->id],
                'closed_at' => $status === DealStatus::Open ? null : fake()->dateTimeBetween('-26 days', 'now'),
                'custom_fields' => ['source' => fake()->randomElement(['Inbound', 'Outbound', 'Recommandation', 'Salon'])],
            ]);

            // Spread creation across the period so the weekly chart has data.
            $deal->created_at = Carbon::instance(fake()->dateTimeBetween('-28 days', 'now'));
            $deal->saveQuietly();
        }
    }

    /**
     * Attach tasks and activities to a sample of records.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Company>  $companies
     * @param  \Illuminate\Database\Eloquent\Collection<int, Contact>  $contacts
     */
    protected function tasksAndActivities(Team $team, User $user, $companies, $contacts): void
    {
        foreach ($companies->take(8) as $company) {
            Task::factory()->create([
                'team_id' => $team->id,
                'taskable_type' => 'company',
                'taskable_id' => $company->id,
                'created_by' => $user->id,
                'assigned_to' => $user->id,
            ]);
            Activity::factory()->create([
                'team_id' => $team->id,
                'subjectable_type' => 'company',
                'subjectable_id' => $company->id,
                'user_id' => $user->id,
            ]);
        }

        foreach ($contacts->take(15) as $contact) {
            Activity::factory()->create([
                'team_id' => $team->id,
                'subjectable_type' => 'contact',
                'subjectable_id' => $contact->id,
                'user_id' => $user->id,
            ]);
        }
    }
}
