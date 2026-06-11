<?php

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['call', 'email', 'meeting', 'note']);

        return [
            'type' => $type,
            'subject' => fake()->sentence(3),
            'body' => fake()->boolean(70) ? fake()->paragraph() : null,
            'occurred_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
