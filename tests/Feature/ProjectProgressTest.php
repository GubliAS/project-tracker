<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_percent_is_fifty_when_one_of_two_equal_weight_tasks_is_done(): void
    {
        $project = Project::factory()->create();
        Task::factory()->for($project)->create(['status' => 'done', 'weight' => 1]);
        Task::factory()->for($project)->create(['status' => 'todo', 'weight' => 1]);

        $this->assertSame(50, $project->fresh()->progressPercent());
    }

    public function test_progress_percent_is_seventy_five_when_heavier_done_task_outweighs_open_task(): void
    {
        $project = Project::factory()->create();
        Task::factory()->for($project)->create(['status' => 'todo', 'weight' => 1]);
        Task::factory()->for($project)->create(['status' => 'done', 'weight' => 3]);

        $this->assertSame(75, $project->fresh()->progressPercent());
    }

    public function test_progress_percent_is_zero_when_project_has_no_tasks(): void
    {
        $this->assertSame(0, Project::factory()->create()->progressPercent());
    }

    public function test_project_list_show_and_dashboard_use_weighted_progress(): void
    {
        $this->signIn();
        $this->withoutVite();

        $project = Project::factory()->create(['status' => 'active']);
        Task::factory()->for($project)->create(['status' => 'todo', 'weight' => 1]);
        Task::factory()->for($project)->create(['status' => 'done', 'weight' => 3]);

        $this->get('/projects')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Projects/Index')
                ->where('projects.0.progress_percent', 75));

        $this->get("/projects/{$project->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Projects/Show')
                ->where('project.progress_percent', 75)
                ->has('members')
                ->has('users'));

        $this->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('runningProjects.0.progress', 75)
                ->where('summaryProjects.0.progress', 75));
    }
}
