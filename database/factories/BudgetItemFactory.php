<?php

namespace Database\Factories;

use App\Models\BudgetItem;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BudgetItem>
 */
class BudgetItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'category' => fake()->randomElement(['Development', 'Design', 'Infrastructure', 'Marketing', 'Testing']),
            'allocated' => 80000,
            'spent' => 25000,
            'status' => 'on-track',
        ];
    }
}
