<?php

namespace Tests\Feature;

use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignee_can_update_status_but_not_title(): void
    {
        $this->signInAs(WorkspaceRole::WorkspaceAdmin);
        $assignee = User::factory()->create();
        $this->workspace?->users()->attach($assignee->id, ['role' => WorkspaceRole::Member->value]);
        $task = Task::factory()->create([
            'project_id' => Project::factory()->create()->id,
            'user_id' => $assignee->id,
            'title' => 'Original title',
            'status' => 'todo',
        ]);

        $this->actingAs($assignee)
            ->withSession(['current_workspace_id' => $this->workspace?->id])
            ->from(route('tasks.index'))
            ->put("/tasks/{$task->id}", ['status' => 'in_progress'])
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
            'title' => 'Original title',
        ]);

        $this->actingAs($assignee)
            ->withSession(['current_workspace_id' => $this->workspace?->id])
            ->put("/tasks/{$task->id}", ['title' => 'Hijacked title'])
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Original title',
        ]);
    }

    public function test_admin_can_update_task_title(): void
    {
        $admin = $this->signInAs(WorkspaceRole::WorkspaceAdmin);
        $task = Task::factory()->create([
            'project_id' => Project::factory()->create()->id,
            'user_id' => $admin->id,
            'title' => 'Original title',
        ]);

        $this->from(route('tasks.index'))
            ->put("/tasks/{$task->id}", ['title' => 'Admin title'])
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Admin title',
        ]);
    }

    public function test_member_who_is_not_the_assignee_cannot_update_status(): void
    {
        $this->signInAs(WorkspaceRole::WorkspaceAdmin);
        $assignee = User::factory()->create();
        $other = User::factory()->create();
        $this->workspace?->users()->attach($assignee->id, ['role' => WorkspaceRole::Member->value]);
        $this->workspace?->users()->attach($other->id, ['role' => WorkspaceRole::Member->value]);
        $task = Task::factory()->create([
            'project_id' => Project::factory()->create()->id,
            'user_id' => $assignee->id,
            'status' => 'todo',
        ]);

        $this->actingAs($other)
            ->withSession(['current_workspace_id' => $this->workspace?->id])
            ->put("/tasks/{$task->id}", ['status' => 'done'])
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'todo',
        ]);
    }
}
