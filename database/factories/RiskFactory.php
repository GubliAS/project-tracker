<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Risk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Risk>
 */
class RiskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(5),
            'category' => 'resource',
            'owner' => fake()->name(),
            'impact' => 'medium',
            'probability' => 'medium',
            'status' => 'open',
            'mitigation_plan' => fake()->sentence(),
            'project_id' => Project::factory(),
        ];
    }
}
