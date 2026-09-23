<?php

namespace Tests\Feature;

use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_switching_workspaces_scopes_the_project_list(): void
    {
        $alpha = Workspace::factory()->create(['name' => 'Alpha']);
        $beta = Workspace::factory()->create(['name' => 'Beta']);
        Project::factory()->create(['workspace_id' => $alpha->id, 'name' => 'Alpha Project']);
        Project::factory()->create(['workspace_id' => $beta->id, 'name' => 'Beta Project']);

        $user = User::factory()->create();
        $alpha->users()->attach($user->id, ['role' => WorkspaceRole::Member->value]);
        $beta->users()->attach($user->id, ['role' => WorkspaceRole::Member->value]);

        $this->actingAs($user)
            ->withSession(['current_workspace_id' => $alpha->id])
            ->get('/projects')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Projects/Index')
                ->has('projects', 1)
                ->where('projects.0.name', 'Alpha Project'));

        $this->actingAs($user)
            ->withSession(['current_workspace_id' => $alpha->id])
            ->post('/workspace/switch', ['workspace_id' => $beta->id])
            ->assertRedirect(route('dashboard'));

        $this->assertSame($beta->id, session('current_workspace_id'));

        $this->get('/projects')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Projects/Index')
                ->has('projects', 1)
                ->where('projects.0.name', 'Beta Project'));
    }

    public function test_platform_admin_switch_changes_dashboard_and_project_list(): void
    {
        $alpha = Workspace::factory()->create(['name' => 'Alpha']);
        $beta = Workspace::factory()->create(['name' => 'Beta']);
        Project::factory()->create(['workspace_id' => $alpha->id, 'name' => 'Alpha Project']);
        Project::factory()->create(['workspace_id' => $beta->id, 'name' => 'Beta Project']);

        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->withSession(['current_workspace_id' => $alpha->id])
            ->get('/projects')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Projects/Index')
                ->has('projects', 1)
                ->where('projects.0.name', 'Alpha Project'));

        $this->actingAs($admin)
            ->withSession(['current_workspace_id' => $alpha->id])
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->has('summaryProjects', 1)
                ->where('summaryProjects.0.title', 'Alpha Project'));

        $this->actingAs($admin)
            ->withSession(['current_workspace_id' => $alpha->id])
            ->post('/workspace/switch', ['workspace_id' => $beta->id])
            ->assertRedirect(route('dashboard'));

        $this->assertSame($beta->id, session('current_workspace_id'));

        $this->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->has('summaryProjects', 1)
                ->where('summaryProjects.0.title', 'Beta Project'));

        $this->get('/projects')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Projects/Index')
                ->has('projects', 1)
                ->where('projects.0.name', 'Beta Project'));
    }

    public function test_regular_users_only_receive_their_memberships_in_the_switcher(): void
    {
        $home = Workspace::factory()->create(['name' => 'Home']);
        Workspace::factory()->create(['name' => 'Other Org']);
        $user = User::factory()->create();
        $home->users()->attach($user->id, ['role' => WorkspaceRole::Member->value]);

        $this->actingAs($user)
            ->withSession(['current_workspace_id' => $home->id])
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('canSwitchWorkspaces', false)
                ->has('workspaces', 1)
                ->where('workspaces.0.name', 'Home'));
    }

    public function test_member_cannot_switch_to_a_workspace_they_do_not_belong_to(): void
    {
        $home = Workspace::factory()->create();
        $foreign = Workspace::factory()->create();
        $user = User::factory()->create();
        $home->users()->attach($user->id, ['role' => WorkspaceRole::Member->value]);

        $this->actingAs($user)
            ->withSession(['current_workspace_id' => $home->id])
            ->post('/workspace/switch', ['workspace_id' => $foreign->id])
            ->assertForbidden();

        $this->assertSame($home->id, session('current_workspace_id'));
    }
}
