<?php

namespace Database\Factories;

use App\Models\Resource;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<resource>
 */
class ResourceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => session('current_workspace_id') ?: Workspace::factory(),
            'name' => fake()->unique()->jobTitle(),
            'email' => fake()->unique()->safeEmail(),
            'type' => fake()->randomElement(['human', 'hardware', 'software', 'material']),
            'role_or_category' => fake()->word(),
            'cost_per_hour' => fake()->randomFloat(2, 10, 250),
            'availability_status' => 'available',
            'availability_percent' => 80,
        ];
    }
}
