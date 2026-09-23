<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WorkspaceCurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_factory_defaults_currency_to_usd(): void
    {
        $workspace = Workspace::factory()->create();

        $this->assertSame(Currency::Usd, $workspace->currency);
        $this->assertDatabaseHas('workspaces', [
            'id' => $workspace->id,
            'currency' => 'USD',
        ]);
    }

    public function test_workspace_admin_can_update_workspace_currency(): void
    {
        $this->signInAs(WorkspaceRole::WorkspaceAdmin);
        $slug = $this->workspace?->slug;

        $this->put('/workspace/settings', [
            'name' => $this->workspace?->name,
            'currency' => Currency::Ghs->value,
        ])->assertRedirect(route('workspace.settings'));

        $workspace = $this->workspace?->fresh();

        $this->assertSame(Currency::Ghs, $workspace?->currency);
        $this->assertSame($slug, $workspace?->slug);
        $this->assertDatabaseHas('workspaces', [
            'id' => $this->workspace?->id,
            'currency' => 'GHS',
        ]);
    }

    public function test_platform_admin_can_update_workspace_currency(): void
    {
        $workspace = Workspace::factory()->create(['currency' => Currency::Usd]);
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put('/workspace/settings', [
                'name' => $workspace->name,
                'currency' => Currency::Ngn->value,
            ])
            ->assertRedirect(route('workspace.settings'));

        $this->assertSame(Currency::Ngn, $workspace->fresh()->currency);
    }

    #[DataProvider('rolesThatCannotChangeCurrency')]
    public function test_non_admin_roles_cannot_update_workspace_currency(WorkspaceRole $role): void
    {
        $this->signInAs($role);

        $this->put('/workspace/settings', [
            'name' => $this->workspace?->name,
            'currency' => Currency::Ghs->value,
        ])->assertForbidden();

        $this->assertSame(Currency::Usd, $this->workspace?->fresh()?->currency);
        $this->assertDatabaseHas('workspaces', [
            'id' => $this->workspace?->id,
            'currency' => 'USD',
        ]);
    }

    public function test_invalid_currency_is_rejected_and_not_saved(): void
    {
        $this->signInAs(WorkspaceRole::WorkspaceAdmin);

        $this->from(route('workspace.settings'))
            ->put('/workspace/settings', [
                'name' => $this->workspace?->name,
                'currency' => 'XXX',
            ])
            ->assertRedirect(route('workspace.settings'))
            ->assertSessionHasErrors(['currency' => 'The selected currency is invalid.']);

        $this->assertSame(Currency::Usd, $this->workspace?->fresh()?->currency);
    }

    public function test_guests_are_redirected_from_settings(): void
    {
        $this->get('/settings')->assertRedirect(route('login'));
    }

    public function test_members_can_view_the_settings_page(): void
    {
        $this->signInAs(WorkspaceRole::Member);

        $this->get('/settings')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Settings/Index')
                ->where('workspace.currency', 'USD')
                ->has('currencies'));
    }

    public function test_workspace_admin_can_update_currency_from_settings(): void
    {
        $this->signInAs(WorkspaceRole::WorkspaceAdmin);

        $this->put('/settings', [
            'currency' => Currency::Ghs->value,
        ])->assertRedirect(route('settings'));

        $this->assertSame(Currency::Ghs, $this->workspace?->fresh()?->currency);
        $this->assertDatabaseHas('workspaces', [
            'id' => $this->workspace?->id,
            'currency' => 'GHS',
        ]);
    }

    public function test_member_cannot_update_currency_from_settings(): void
    {
        $this->signInAs(WorkspaceRole::Member);

        $this->put('/settings', [
            'currency' => Currency::Ghs->value,
        ])->assertForbidden();

        $this->assertSame(Currency::Usd, $this->workspace?->fresh()?->currency);
    }

    public function test_existing_project_keeps_currency_when_workspace_default_changes(): void
    {
        $this->signInAs(WorkspaceRole::WorkspaceAdmin);
        $project = Project::factory()->create([
            'workspace_id' => $this->workspace?->id,
            'currency' => Currency::Usd,
        ]);

        $this->put('/settings', [
            'currency' => Currency::Ghs->value,
        ])->assertRedirect(route('settings'));

        $this->assertSame(Currency::Usd, $project->fresh()->currency);
        $this->assertSame(Currency::Ghs, $this->workspace?->fresh()?->currency);
    }

    public function test_create_project_page_exposes_workspace_currency_symbol(): void
    {
        $workspace = Workspace::factory()->create(['currency' => Currency::Ghs]);

        $this->signIn(null, $workspace);

        $this->get(route('projects.create'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Projects/Create')
                ->where('currency.code', 'GHS')
                ->where('currency.symbol', 'GH₵'));
    }

    /**
     * @return array<string, array{0: WorkspaceRole}>
     */
    public static function rolesThatCannotChangeCurrency(): array
    {
        return [
            'project_manager' => [WorkspaceRole::ProjectManager],
            'member' => [WorkspaceRole::Member],
            'viewer' => [WorkspaceRole::Viewer],
        ];
    }
}
