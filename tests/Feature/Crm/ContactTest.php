<?php

namespace Tests\Feature\Crm;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    protected function recordFor(string $class, Team $team, array $attributes): mixed
    {
        $model = new $class;
        $model->forceFill([...$attributes, 'team_id' => $team->id]);
        $model->save();

        return $model;
    }

    public function test_a_contact_can_be_created(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('contacts.store', ['current_team' => $user->currentTeam->slug]), [
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'email' => 'jean@example.com',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('contacts', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'team_id' => $user->currentTeam->id,
        ]);
    }

    public function test_a_contact_cannot_be_linked_to_another_teams_company(): void
    {
        $owner = User::factory()->create();
        $foreignCompany = $this->recordFor(Company::class, $owner->currentTeam, ['name' => 'Foreign']);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('contacts.store', ['current_team' => $user->currentTeam->slug]), [
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'company_id' => $foreignCompany->id,
            ])
            ->assertSessionHasErrors('company_id');
    }

    public function test_a_user_cannot_view_another_teams_contact(): void
    {
        $owner = User::factory()->create();
        $contact = $this->recordFor(Contact::class, $owner->currentTeam, [
            'first_name' => 'Secret',
            'last_name' => 'Person',
        ]);

        $intruder = User::factory()->create();

        $this->actingAs($intruder)
            ->get(route('contacts.show', ['current_team' => $intruder->currentTeam->slug, 'contact' => $contact->id]))
            ->assertNotFound();
    }
}
