<?php

namespace Database\Factories;

use App\Models\Changelog;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Changelog>
 */
class ChangelogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => session('current_workspace_id') ?: Workspace::factory(),
            'version' => 'v'.fake()->numerify('#.#.#'),
            'title' => fake()->sentence(4),
            'requestor' => fake()->name(),
            'description' => fake()->paragraph(),
            'type' => 'feature',
            'approval_status' => 'pending',
            'impact' => 'medium',
            'release_date' => fake()->date(),
        ];
    }
}
