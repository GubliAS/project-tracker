<?php

namespace Database\Factories;

use App\Models\BacklogItem;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BacklogItem>
 */
class BacklogItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'type' => fake()->randomElement(['epic', 'feature', 'story']),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'points' => fake()->randomElement([3, 5, 8, 13, 21]),
            'status' => 'backlog',
        ];
    }
}
