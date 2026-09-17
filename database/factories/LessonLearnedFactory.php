<?php

namespace Database\Factories;

use App\Models\LessonLearned;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonLearned>
 */
class LessonLearnedFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(5),
            'category' => 'Delivery',
            'impact_level' => 'medium',
            'recommendation' => fake()->paragraph(),
            'project_id' => Project::factory(),
        ];
    }
}
