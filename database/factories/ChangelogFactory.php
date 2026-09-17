<?php

namespace Database\Factories;

use App\Models\Changelog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Changelog>
 */
class ChangelogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'version' => 'v'.fake()->numerify('#.#.#'),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'type' => 'feature',
            'release_date' => fake()->date(),
        ];
    }
}
