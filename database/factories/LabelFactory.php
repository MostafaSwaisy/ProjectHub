<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Label>
 */
class LabelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $colors = [
            '#EF4444', // Red
            '#F97316', // Orange
            '#EAB308', // Yellow
            '#22C55E', // Green
            '#14B8A6', // Teal
            '#3B82F6', // Blue
            '#6366F1', // Indigo
            '#A855F7', // Purple
            '#EC4899', // Pink
            '#6B7280', // Gray
        ];

        return [
            'project_id' => Project::factory(),
            'name' => fake()->randomElement(['Bug', 'Feature', 'Enhancement', 'Documentation', 'Testing', 'Urgent', 'Low Priority']),
            'color' => fake()->randomElement($colors),
        ];
    }
}
