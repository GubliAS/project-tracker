<?php

namespace Tests\Feature;

use App\Enums\WorkspaceRole;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WorkspaceRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_workspace_and_project_writes(): void
    {
        $this->get('/workspace')->assertRedirect(route('login'));
        $this->post('/projects', [
            'name' => 'Secret',
            'status' => 'planning',
        ])->assertRedirect(route('login'));
    }

    public function test_viewer_cannot_create_a_project(): void
    {
        $this->signInAs(WorkspaceRole::Viewer);

        $this->post('/projects', [
            'name' => 'Viewer Project',
            'status' => 'planning',
        ])->assertForbidden();

        $this->assertDatabaseMissing('projects', ['name' => 'Viewer Project']);
    }

    public function test_member_cannot_invite_to_the_workspace(): void
    {
        $this->signInAs(WorkspaceRole::Member);

        $this->post('/workspace/members/invite', [
            'email' => 'new.member@example.com',
            'role' => WorkspaceRole::Member->value,
        ])->assertForbidden();

        $this->assertDatabaseCount('invitations', 0);
    }

    public function test_workspace_admin_can_invite_a_member(): void
    {
        Notification::fake();

        $this->signInAs(WorkspaceRole::WorkspaceAdmin);

        $this->post('/workspace/members/invite', [
            'email' => 'new.member@example.com',
            'role' => WorkspaceRole::Member->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('invitations', [
            'email' => 'new.member@example.com',
            'workspace_id' => $this->workspace?->id,
            'role' => WorkspaceRole::Member->value,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'new.member@example.com',
            'must_set_password' => true,
        ]);
    }

    public function test_platform_admin_can_list_all_workspaces(): void
    {
        $alpha = Workspace::factory()->create(['name' => 'Alpha']);
        $beta = Workspace::factory()->create(['name' => 'Beta']);
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin/workspaces')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Workspaces')
                ->has('workspaces', Workspace::query()->count()));

        $this->assertTrue(Workspace::query()->whereKey([$alpha->id, $beta->id])->count() === 2);
    }

    public function test_user_cannot_view_a_project_from_another_workspace(): void
    {
        $workspaceA = Workspace::factory()->create();
        $workspaceB = Workspace::factory()->create();
        $userA = User::factory()->create();
        $workspaceA->users()->attach($userA->id, ['role' => WorkspaceRole::Member->value]);
        $foreignProject = Project::factory()->create([
            'workspace_id' => $workspaceB->id,
            'name' => 'Foreign Project',
        ]);

        $this->actingAs($userA)
            ->withSession(['current_workspace_id' => $workspaceA->id])
            ->get("/projects/{$foreignProject->id}")
            ->assertNotFound();

        $this->actingAs($userA)
            ->withSession(['current_workspace_id' => $workspaceA->id])
            ->get('/projects')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Projects/Index')
                ->has('projects', 0));
    }

    public function test_invited_user_sets_a_password_to_join_the_workspace(): void
    {
        Notification::fake();

        $this->signInAs(WorkspaceRole::WorkspaceAdmin);

        $this->post('/workspace/members/invite', [
            'email' => 'join@example.com',
            'role' => WorkspaceRole::Viewer->value,
        ])->assertRedirect();

        $invitation = Invitation::query()->firstOrFail();
        $this->post('/logout');

        $this->post('/invitations/'.$invitation->token, [
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard'));

        $invitee = User::query()->where('email', 'join@example.com')->firstOrFail();

        $this->assertTrue($invitee->belongsToWorkspace($this->workspace));
        $this->assertSame(WorkspaceRole::Viewer, $invitee->roleIn($this->workspace));
        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->assertFalse($invitee->must_set_password);
    }
}
