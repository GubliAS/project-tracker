<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\QualityCheck;
use App\Models\Resource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_index_renders_kanban_with_related_data(): void
    {
        $project = Project::factory()->create(['name' => 'Website Rebuild']);
        $assignee = User::factory()->create(['name' => 'Ada Lovelace']);
        Task::factory()->create([
            'title' => 'Design homepage',
            'project_id' => $project->id,
            'user_id' => $assignee->id,
            'status' => 'todo',
        ]);

        $this->get('/tasks')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Tasks/Index')
                ->has('tasks', 1)
                ->where('tasks.0.title', 'Design homepage')
                ->where('tasks.0.project.name', 'Website Rebuild')
                ->where('tasks.0.user.name', 'Ada Lovelace')
                ->has('projects')
                ->has('users'));
    }

    public function test_task_can_be_created_updated_and_deleted(): void
    {
        $project = Project::factory()->create();
        $user = User::factory()->create();

        $this->post('/tasks', [
            'title' => 'Write API tests',
            'description' => 'Cover store and update paths',
            'project_id' => $project->id,
            'user_id' => $user->id,
            'priority' => 'high',
            'due_date' => '2026-09-30',
        ])->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'title' => 'Write API tests',
            'status' => 'todo',
            'priority' => 'high',
        ]);

        $task = Task::query()->first();

        $this->put("/tasks/{$task->id}", [
            'status' => 'in_progress',
        ])->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
        ]);

        $this->delete("/tasks/{$task->id}")->assertRedirect(route('tasks.index'));
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_resources_can_be_managed(): void
    {
        $this->get('/resources')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Resources/Index')->has('resources'));

        $this->post('/resources', [
            'name' => 'QA Engineer',
            'type' => 'human',
            'role_or_category' => 'Quality',
            'cost_per_hour' => 85.5,
            'availability_status' => 'available',
        ])->assertRedirect(route('resources.index'));

        $resource = Resource::query()->first();

        $this->put("/resources/{$resource->id}", [
            'availability_status' => 'allocated',
        ])->assertRedirect(route('resources.index'));

        $this->assertDatabaseHas('resources', [
            'id' => $resource->id,
            'availability_status' => 'allocated',
        ]);

        $this->delete("/resources/{$resource->id}")->assertRedirect(route('resources.index'));
        $this->assertDatabaseMissing('resources', ['id' => $resource->id]);
    }

    public function test_quality_checks_can_be_managed(): void
    {
        $project = Project::factory()->create(['name' => 'Mobile App']);

        $this->get('/quality')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Quality/Index')
                ->has('qualityChecks')
                ->has('summary'));

        $this->post('/quality', [
            'project_id' => $project->id,
            'title' => 'Sprint 1 regression',
            'check_type' => 'testing',
            'status' => 'pending',
            'notes' => 'Cover checkout flow',
        ])->assertRedirect(route('quality.index'));

        $check = QualityCheck::query()->first();

        $this->put("/quality/{$check->id}", [
            'status' => 'passed',
            'notes' => 'All cases passed',
        ])->assertRedirect(route('quality.index'));

        $this->assertDatabaseHas('quality_checks', [
            'id' => $check->id,
            'status' => 'passed',
        ]);

        $this->delete("/quality/{$check->id}")->assertRedirect(route('quality.index'));
        $this->assertDatabaseMissing('quality_checks', ['id' => $check->id]);
    }

    public function test_reports_index_aggregates_system_statistics(): void
    {
        $project = Project::factory()->create();
        Task::factory()->create(['project_id' => $project->id, 'status' => 'done']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'todo']);
        Resource::factory()->create(['availability_status' => 'available']);
        Resource::factory()->create(['availability_status' => 'allocated']);
        QualityCheck::factory()->create(['project_id' => $project->id, 'status' => 'passed']);
        QualityCheck::factory()->create(['project_id' => $project->id, 'status' => 'pending']);

        $this->get('/reports')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Reports/Index')
                ->where('stats.total_projects', 1)
                ->where('stats.total_tasks', 2)
                ->where('stats.completion_rate', 50)
                ->where('stats.pending_quality_audits', 1)
                ->where('stats.active_resources', 2)
                ->where('stats.quality_pass_rate', 50)
                ->has('tasksByStatus')
                ->has('resourceUtilization')
                ->has('qualityByStatus')
                ->has('projectProgress')
                ->has('teamWorkloads'));
    }
}
