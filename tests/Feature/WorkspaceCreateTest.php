<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\WorkspaceRole;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_an_additional_workspace_and_switches_to_it(): void
    {
        $user = $this->signInAs(WorkspaceRole::Member);
        $home = $this->workspace;

        $this->post('/workspaces', [
            'name' => 'Second Company',
            'currency' => Currency::Ghs->value,
        ])->assertRedirect(route('dashboard'));

        $created = Workspace::query()->where('name', 'Second Company')->first();

        $this->assertNotNull($created);
        $this->assertSame(Currency::Ghs, $created->currency);
        $this->assertTrue($user->belongsToWorkspace($created));
        $this->assertSame(WorkspaceRole::WorkspaceAdmin, $user->roleIn($created));
        $this->assertSame(WorkspaceRole::Member, $user->roleIn($home));
        $this->assertFalse($user->fresh()->is_platform_admin);
        $this->assertSame($created->id, session('current_workspace_id'));
        $this->assertSame(2, $user->workspaces()->count());

        $this->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('currentWorkspace.id', $created->id)
                ->where('currentWorkspace.name', 'Second Company')
                ->has('workspaces', 2));
    }

    public function test_guest_is_redirected_from_workspace_creation(): void
    {
        $this->post('/workspaces', [
            'name' => 'Guest Company',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('workspaces', ['name' => 'Guest Company']);
    }

    public function test_empty_workspace_create_payload_fails_validation(): void
    {
        $this->signIn();

        $this->from('/dashboard')->post('/workspaces', [])
            ->assertRedirect('/dashboard')
            ->assertSessionHasErrors('name');
    }

    public function test_duplicate_workspace_name_for_the_same_user_fails_validation(): void
    {
        $this->signIn(null, Workspace::factory()->create(['name' => 'Acme Delivery']));

        $this->from('/dashboard')->post('/workspaces', [
            'name' => 'Acme Delivery',
        ])->assertRedirect('/dashboard')->assertSessionHasErrors('name');

        $this->assertSame(1, Workspace::query()->where('name', 'Acme Delivery')->count());
    }

    public function test_invalid_currency_is_rejected(): void
    {
        $this->signIn();

        $this->from('/dashboard')->post('/workspaces', [
            'name' => 'Currency Co',
            'currency' => 'XXX',
        ])->assertRedirect('/dashboard')->assertSessionHasErrors('currency');

        $this->assertDatabaseMissing('workspaces', ['name' => 'Currency Co']);
    }

    public function test_workspace_admin_cannot_open_the_platform_admin_page(): void
    {
        $this->signInAs(WorkspaceRole::WorkspaceAdmin);

        $this->get('/admin')->assertForbidden();
        $this->get('/admin/workspaces')->assertForbidden();
        $this->post('/admin/workspaces', ['name' => 'Platform Only'])->assertForbidden();

        $this->assertDatabaseMissing('workspaces', ['name' => 'Platform Only']);
    }
}
