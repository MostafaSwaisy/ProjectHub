<?php

namespace Database\Factories;

use App\Models\Column;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
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
            'column_id' => Column::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'assignee_id' => fake()->optional(0.5)->randomElement(User::pluck('id')->toArray()) ?: User::factory(),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'due_date' => fake()->optional(0.7)->dateTime(),
            'position' => fake()->numberBetween(1, 100),
        ];
    }
}
