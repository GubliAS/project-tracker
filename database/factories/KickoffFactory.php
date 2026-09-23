<?php

namespace Database\Factories;

use App\Models\Kickoff;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kickoff>
 */
class KickoffFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'scheduled_on' => now()->toDateString(),
            'attendees' => fake()->numberBetween(4, 16),
            'status' => 'scheduled',
            'objectives' => [
                ['text' => 'Define project scope and deliverables', 'completed' => false],
            ],
        ];
    }
}
