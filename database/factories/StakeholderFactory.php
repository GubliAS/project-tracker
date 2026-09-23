<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Stakeholder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stakeholder>
 */
class StakeholderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->name(),
            'role' => fake()->jobTitle(),
            'department' => fake()->randomElement(['Executive', 'Product', 'Engineering', 'Quality', 'Operations']),
            'influence' => fake()->randomElement(['low', 'medium', 'high']),
            'interest' => fake()->randomElement(['low', 'medium', 'high']),
        ];
    }
}
