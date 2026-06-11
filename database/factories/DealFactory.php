<?php

namespace Database\Factories;

use App\Domain\Shared\Enums\DealStatus;
use App\Models\Deal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deal>
 */
class DealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Refonte', 'Licence', 'Abonnement', 'Conseil', 'Intégration'])
                .' '.fake()->company(),
            'amount' => fake()->numberBetween(2, 120) * 1000,
            'currency' => 'EUR',
            'status' => DealStatus::Open,
            'expected_close_date' => fake()->dateTimeBetween('now', '+3 months'),
            'position' => 0,
            'custom_fields' => null,
        ];
    }
}
