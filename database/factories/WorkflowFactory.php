<?php

namespace Database\Factories;

use App\Models\Workflow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workflow>
 */
class WorkflowFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Workflow',
            'stages' => ['Backlog', 'In Progress', 'Review', 'Done'],
        ];
    }
}
