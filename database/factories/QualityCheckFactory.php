<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\QualityCheck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QualityCheck>
 */
class QualityCheckFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(5),
            'check_type' => fake()->randomElement(['code_review', 'testing', 'security_audit', 'compliance']),
            'status' => 'pending',
            'priority' => 'medium',
            'last_run_at' => now(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
