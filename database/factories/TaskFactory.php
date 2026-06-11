<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->boolean(60) ? fake()->paragraph() : null,
            'due_at' => fake()->boolean(70) ? fake()->dateTimeBetween('-1 week', '+3 weeks') : null,
            'completed_at' => null,
            'priority' => fake()->randomElement(['low', 'normal', 'high']),
        ];
    }

    /**
     * Indicate the task is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => fake()->dateTimeBetween('-2 weeks', 'now'),
        ]);
    }
}
