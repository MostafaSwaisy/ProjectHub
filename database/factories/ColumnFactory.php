<?php

namespace Database\Factories;

use App\Models\Board;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Column>
 */
class ColumnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Note: Board::factory() would auto-create 5 default columns via Board::boot()
            // Avoid using Board::factory() here to prevent duplicates.
            // Instead, provide board_id explicitly when using this factory.
            // Example: Column::factory(['board_id' => $board->id])->create();
            'board_id' => null, // Must be provided explicitly
            'title' => fake()->word(),
            'position' => fake()->numberBetween(1, 10),
            'wip_limit' => 0,
        ];
    }
}
