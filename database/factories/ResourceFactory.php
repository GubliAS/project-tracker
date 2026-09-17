<?php

namespace Database\Factories;

use App\Models\Resource;
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
            'name' => fake()->unique()->jobTitle(),
            'type' => fake()->randomElement(['human', 'hardware', 'software', 'material']),
            'role_or_category' => fake()->word(),
            'cost_per_hour' => fake()->randomFloat(2, 10, 250),
            'availability_status' => 'available',
        ];
    }
}
