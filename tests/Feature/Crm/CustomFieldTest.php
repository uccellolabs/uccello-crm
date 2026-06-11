<?php

namespace Tests\Feature\Crm;

use App\Domain\Shared\Enums\CustomFieldType;
use App\Domain\Shared\Enums\TeamRole;
use App\Models\Company;
use App\Models\Contact;
use App\Models\CustomFieldDefinition;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomFieldTest extends TestCase
{
    use RefreshDatabase;

    protected function definitionFor(Team $team, array $attributes): CustomFieldDefinition
    {
        $definition = new CustomFieldDefinition;
        $definition->forceFill([
            'entity_type' => 'company',
            'key' => 'segment',
            'label' => 'Segment',
            'type' => CustomFieldType::Text,
            'is_required' => false,
            'is_filterable' => false,
            'position' => 0,
            ...$attributes,
            'team_id' => $team->id,
        ]);
        $definition->save();

        return $definition;
    }

    public function test_an_admin_can_create_a_custom_field(): void
    {
        $user = User::factory()->create(); // owner of personal team

        $this->actingAs($user)
            ->post(route('custom-fields.store', ['current_team' => $user->currentTeam->slug]), [
                'entity_type' => 'company',
                'label' => 'Chiffre d’affaires',
                'type' => 'number',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('custom_field_definitions', [
            'team_id' => $user->currentTeam->id,
            'entity_type' => 'company',
            'label' => 'Chiffre d’affaires',
            'type' => 'number',
        ]);
    }

    public function test_a_member_cannot_manage_custom_fields(): void
    {
        $owner = User::factory()->create();
        $team = $owner->currentTeam;

        $member = User::factory()->create();
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);
        $member->switchTeam($team);

        $this->actingAs($member)
            ->get(route('custom-fields.index', ['current_team' => $team->slug]))
            ->assertForbidden();

        $this->actingAs($member)
            ->post(route('custom-fields.store', ['current_team' => $team->slug]), [
                'entity_type' => 'company',
                'label' => 'Test',
                'type' => 'text',
            ])
            ->assertForbidden();
    }

    public function test_a_required_custom_field_is_enforced_on_company_create(): void
    {
        $user = User::factory()->create();
        $this->definitionFor($user->currentTeam, [
            'key' => 'segment',
            'label' => 'Segment',
            'is_required' => true,
        ]);

        $this->actingAs($user)
            ->post(route('companies.store', ['current_team' => $user->currentTeam->slug]), [
                'name' => 'Acme',
                'custom_fields' => [],
            ])
            ->assertSessionHasErrors('custom_fields.segment');
    }

    public function test_custom_field_values_are_stored_and_normalized(): void
    {
        $user = User::factory()->create();
        $this->definitionFor($user->currentTeam, [
            'key' => 'revenue',
            'label' => 'CA',
            'type' => CustomFieldType::Number,
        ]);

        $this->actingAs($user)
            ->post(route('companies.store', ['current_team' => $user->currentTeam->slug]), [
                'name' => 'Acme',
                'custom_fields' => ['revenue' => '1500'],
            ])
            ->assertRedirect();

        $company = Company::withoutGlobalScope('team')
            ->where('team_id', $user->currentTeam->id)
            ->sole();
        $this->assertSame(1500, $company->custom_fields['revenue']);
    }

    public function test_a_deleted_field_key_can_be_recreated(): void
    {
        $user = User::factory()->create();
        $slug = $user->currentTeam->slug;

        $this->actingAs($user)
            ->post(route('custom-fields.store', ['current_team' => $slug]), [
                'entity_type' => 'company',
                'label' => 'Budget',
                'type' => 'number',
            ])
            ->assertRedirect();

        $field = CustomFieldDefinition::withoutGlobalScope('team')->sole();

        $this->actingAs($user)
            ->delete(route('custom-fields.destroy', ['current_team' => $slug, 'customField' => $field->id]))
            ->assertRedirect();

        // Re-creating the same label must not hit the unique index.
        $this->actingAs($user)
            ->post(route('custom-fields.store', ['current_team' => $slug]), [
                'entity_type' => 'company',
                'label' => 'Budget',
                'type' => 'number',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    }

    public function test_a_required_checkbox_must_be_checked(): void
    {
        $user = User::factory()->create();
        $this->definitionFor($user->currentTeam, [
            'key' => 'consent',
            'label' => 'Consentement',
            'type' => CustomFieldType::Checkbox,
            'is_required' => true,
        ]);

        $this->actingAs($user)
            ->post(route('companies.store', ['current_team' => $user->currentTeam->slug]), [
                'name' => 'Acme',
                'custom_fields' => ['consent' => false],
            ])
            ->assertSessionHasErrors('custom_fields.consent');
    }

    public function test_creating_a_relation_field_requires_a_target_module(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('custom-fields.store', ['current_team' => $user->currentTeam->slug]), [
                'entity_type' => 'company',
                'label' => 'Partenaire',
                'type' => 'relation',
            ])
            ->assertSessionHasErrors('related_module');
    }

    public function test_a_relation_field_stores_the_target_record_id(): void
    {
        $user = User::factory()->create();
        $this->definitionFor($user->currentTeam, [
            'key' => 'partenaire',
            'label' => 'Partenaire',
            'type' => CustomFieldType::Relation,
            'options' => ['related_module' => 'contact'],
        ]);

        $contact = new Contact;
        $contact->forceFill([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'team_id' => $user->currentTeam->id,
        ]);
        $contact->save();

        $this->actingAs($user)
            ->post(route('companies.store', ['current_team' => $user->currentTeam->slug]), [
                'name' => 'Acme',
                'custom_fields' => ['partenaire' => (string) $contact->id],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $company = Company::withoutGlobalScope('team')
            ->where('team_id', $user->currentTeam->id)
            ->sole();
        $this->assertSame($contact->id, $company->custom_fields['partenaire']);
    }

    public function test_a_relation_field_rejects_records_from_another_team(): void
    {
        $owner = User::factory()->create();
        $foreignContact = new Contact;
        $foreignContact->forceFill([
            'first_name' => 'Intrus',
            'last_name' => 'Étranger',
            'team_id' => $owner->currentTeam->id,
        ]);
        $foreignContact->save();

        $user = User::factory()->create();
        $this->definitionFor($user->currentTeam, [
            'key' => 'partenaire',
            'label' => 'Partenaire',
            'type' => CustomFieldType::Relation,
            'options' => ['related_module' => 'contact'],
        ]);

        $this->actingAs($user)
            ->post(route('companies.store', ['current_team' => $user->currentTeam->slug]), [
                'name' => 'Acme',
                'custom_fields' => ['partenaire' => (string) $foreignContact->id],
            ])
            ->assertSessionHasErrors('custom_fields.partenaire');
    }

    public function test_a_select_custom_field_rejects_values_outside_its_choices(): void
    {
        $user = User::factory()->create();
        $this->definitionFor($user->currentTeam, [
            'key' => 'tier',
            'label' => 'Niveau',
            'type' => CustomFieldType::Select,
            'options' => ['choices' => [
                ['value' => 'gold', 'label' => 'Gold'],
                ['value' => 'silver', 'label' => 'Silver'],
            ]],
        ]);

        $this->actingAs($user)
            ->post(route('companies.store', ['current_team' => $user->currentTeam->slug]), [
                'name' => 'Acme',
                'custom_fields' => ['tier' => 'bronze'],
            ])
            ->assertSessionHasErrors('custom_fields.tier');
    }
}
