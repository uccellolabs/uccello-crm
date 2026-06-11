<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();
        $domain = fake()->domainName();

        return [
            'name' => $name,
            'domain' => $domain,
            'industry' => fake()->randomElement([
                'SaaS', 'Conseil', 'Industrie', 'Commerce', 'Santé',
                'Finance', 'Immobilier', 'Éducation', 'Logistique', 'Média',
            ]),
            'phone' => fake()->phoneNumber(),
            'website' => 'https://'.$domain,
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'France',
            'custom_fields' => null,
        ];
    }
}
