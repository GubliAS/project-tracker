<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true).' Project',
            'description' => fake()->sentence(),
            'status' => 'planning',
            'team' => fake()->randomElement(['Development Team', 'Marketing Team', 'Design Team', 'QA Team']),
            'client' => fake()->company(),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'project_type' => fake()->randomElement(['agile', 'predictive', 'hybrid']),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'budget' => fake()->numberBetween(15000, 120000),
            'spent' => fake()->numberBetween(0, 14000),
            'settings' => [],
        ];
    }
}
