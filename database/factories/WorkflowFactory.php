<?php

namespace Database\Factories;

use App\Models\Workflow;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workflow>
 */
class WorkflowFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => session('current_workspace_id') ?: Workspace::factory(),
            'name' => fake()->words(3, true).' Workflow',
            'stages' => ['Backlog', 'In Progress', 'Review', 'Done'],
        ];
    }
}
