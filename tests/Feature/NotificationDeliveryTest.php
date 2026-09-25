<?php

namespace Tests\Feature;

use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectCreated;
use App\Notifications\TaskAssigned;
use App\Notifications\WorkspaceInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigning_a_task_emails_the_assignee(): void
    {
        Notification::fake();

        $this->signInAs(WorkspaceRole::WorkspaceAdmin);
        $assignee = User::factory()->create();
        $this->workspace?->users()->attach($assignee->id, ['role' => WorkspaceRole::Member->value]);
        $project = Project::factory()->create();

        $this->from(route('tasks.index'))->post('/tasks', [
            'title' => 'Ship notifications',
            'project_id' => $project->id,
            'user_id' => $assignee->id,
            'priority' => 'medium',
        ])->assertRedirect(route('tasks.index'));

        Notification::assertSentTo($assignee, TaskAssigned::class);
    }

    public function test_creating_a_project_emails_workspace_admins(): void
    {
        Notification::fake();

        $admin = $this->signInAs(WorkspaceRole::WorkspaceAdmin);

        $this->post('/projects', [
            'name' => 'Client Portal',
            'status' => 'planning',
        ])->assertRedirect();

        Notification::assertSentTo($admin, ProjectCreated::class);
    }

    public function test_workspace_invite_still_sends_mail(): void
    {
        Notification::fake();

        $this->signInAs(WorkspaceRole::WorkspaceAdmin);

        $this->post('/workspace/members/invite', [
            'email' => 'join@example.com',
            'name' => 'Join User',
            'role' => WorkspaceRole::Member->value,
        ])->assertRedirect();

        $invitee = User::query()->where('email', 'join@example.com')->firstOrFail();

        Notification::assertSentTo($invitee, WorkspaceInvitation::class);
    }
}
