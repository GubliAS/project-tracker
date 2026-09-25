<?php

namespace Tests\Feature;

use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WriteValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_auth_payloads_fail_validation(): void
    {
        $this->from('/login')->post('/login', [])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email', 'password']);

        $this->from('/register')->post('/register', [])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->from('/forgot-password')->post('/forgot-password', [])
            ->assertRedirect('/forgot-password')
            ->assertSessionHasErrors(['email']);
    }

    public function test_duplicate_registration_email_fails_validation(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->from('/register')->post('/register', [
            'name' => 'New Person',
            'email' => 'taken@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/register')->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(1, User::query()->where('email', 'taken@example.com')->count());
    }

    public function test_empty_profile_and_password_payloads_fail_validation(): void
    {
        $this->signIn();

        $this->from('/profile')->patch('/profile', [])
            ->assertRedirect('/profile')
            ->assertSessionHasErrors(['name', 'email']);

        $this->from('/profile')->put('/password', [])
            ->assertRedirect('/profile')
            ->assertSessionHasErrors(['current_password', 'password']);
    }

    public function test_empty_invite_payload_fails_validation(): void
    {
        $this->signInAs(WorkspaceRole::WorkspaceAdmin);

        $this->from('/workspace/members')
            ->post('/workspace/members/invite', [])
            ->assertRedirect('/workspace/members')
            ->assertSessionHasErrors(['email', 'role']);
    }

    public function test_empty_workspace_settings_payload_fails_validation(): void
    {
        $this->signInAs(WorkspaceRole::WorkspaceAdmin);

        $this->from('/workspace/settings')->put('/workspace/settings', [])
            ->assertRedirect('/workspace/settings')
            ->assertSessionHasErrors(['name', 'currency']);

        $this->from('/settings')->put('/settings', [])
            ->assertRedirect('/settings')
            ->assertSessionHasErrors(['currency']);

        $this->from('/dashboard')->post('/workspace/switch', [])
            ->assertRedirect('/dashboard')
            ->assertSessionHasErrors(['workspace_id']);
    }

    public function test_empty_task_payload_fails_validation(): void
    {
        $this->signInAs(WorkspaceRole::WorkspaceAdmin);

        $this->from(route('tasks.index'))
            ->post('/tasks', [])
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHasErrors(['title', 'priority']);
    }

    public function test_empty_live_module_payloads_fail_validation(): void
    {
        $this->signInAs(WorkspaceRole::WorkspaceAdmin);

        $this->from('/initiation/kickoff')->post('/initiation/kickoff', [])
            ->assertRedirect('/initiation/kickoff')
            ->assertSessionHasErrors(['project_id', 'scheduled_on', 'attendees', 'status']);

        $this->from('/agile/sprints')->post('/agile/sprints', [])
            ->assertRedirect('/agile/sprints')
            ->assertSessionHasErrors(['name', 'status']);

        $this->from('/resources')->post('/resources', [])
            ->assertRedirect('/resources')
            ->assertSessionHasErrors(['name', 'type', 'cost_per_hour', 'availability_status']);

        $this->from('/quality/risks')->post('/quality/risks', [])
            ->assertRedirect('/quality/risks')
            ->assertSessionHasErrors(['title', 'impact', 'probability', 'status']);

        $this->from('/reports/lessons-learned')->post('/reports/lessons-learned', [])
            ->assertRedirect('/reports/lessons-learned')
            ->assertSessionHasErrors(['title', 'category', 'impact_level', 'recommendation']);

        $this->from('/chat')->post('/chat', [])
            ->assertRedirect('/chat')
            ->assertSessionHasErrors(['project_id', 'message']);
    }

    public function test_empty_document_payload_and_bad_file_fail_validation(): void
    {
        $this->signInAs(WorkspaceRole::WorkspaceAdmin);

        $this->from('/reports/documents')->post('/reports/documents', [])
            ->assertRedirect('/reports/documents')
            ->assertSessionHasErrors(['file', 'category']);

        $this->from('/reports/documents')->post('/reports/documents', [
            'file' => UploadedFile::fake()->create('malware.exe', 20),
            'category' => 'planning',
        ])->assertRedirect('/reports/documents')->assertSessionHasErrors('file');

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_member_cannot_update_a_project(): void
    {
        $this->signInAs(WorkspaceRole::Member);
        $project = Project::factory()->create(['name' => 'Locked']);

        $this->put("/projects/{$project->id}", [
            'name' => 'Hijacked',
            'status' => 'active',
        ])->assertForbidden();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Locked',
        ]);
    }

    public function test_viewer_cannot_write_ops_or_member_content(): void
    {
        $this->signInAs(WorkspaceRole::Viewer);
        $project = Project::factory()->create();

        $this->post('/initiation/kickoff', [
            'project_id' => $project->id,
            'scheduled_on' => '2026-09-24',
            'attendees' => 4,
            'status' => 'scheduled',
        ])->assertForbidden();

        $this->post('/chat', [
            'project_id' => $project->id,
            'message' => 'Should not save',
        ])->assertForbidden();

        $this->assertDatabaseCount('kickoffs', 0);
        $this->assertDatabaseCount('chat_messages', 0);
    }
}
