<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'instructor_id' => User::factory(),
            'timeline_status' => fake()->randomElement(['behind', 'on_track', 'ahead']),
            'budget_status' => fake()->randomElement(['over_budget', 'on_budget', 'under_budget']),
        ];
    }
}
