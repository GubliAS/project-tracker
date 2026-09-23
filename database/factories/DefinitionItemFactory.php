<?php

namespace Database\Factories;

use App\Models\DefinitionItem;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DefinitionItem>
 */
class DefinitionItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'kind' => fake()->randomElement(['dor', 'dod']),
            'text' => fake()->sentence(),
            'is_checked' => false,
        ];
    }
}
