<?php

namespace Tests\Feature;

use App\Enums\WorkspaceRole;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\WorkspaceInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WorkspaceInviteTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_admin_invite_creates_a_user_and_sends_mail(): void
    {
        Notification::fake();

        $this->signInAs(WorkspaceRole::WorkspaceAdmin);

        $response = $this->post('/workspace/members/invite', [
            'email' => 'new.member@example.com',
            'name' => 'New Member',
            'role' => WorkspaceRole::Member->value,
        ]);

        $response->assertRedirect();

        $invitee = User::query()->where('email', 'new.member@example.com')->first();
        $invitation = Invitation::query()->firstOrFail();
        $path = '/invitations/'.$invitation->token;

        $this->assertNotNull($invitee);
        $this->assertSame('New Member', $invitee->name);
        $this->assertTrue($invitee->must_set_password);
        $this->assertNull($invitee->email_verified_at);
        $this->assertTrue($invitee->belongsToWorkspace($this->workspace));
        $this->assertSame(WorkspaceRole::Member, $invitee->roleIn($this->workspace));
        $this->assertDatabaseHas('invitations', [
            'email' => 'new.member@example.com',
            'workspace_id' => $this->workspace?->id,
            'role' => WorkspaceRole::Member->value,
        ]);

        $response->assertSessionHas('message', function (string $message) use ($path): bool {
            return str_contains($message, $path)
                && str_contains($message, 'APP_URL');
        });

        Notification::assertSentTo($invitee, WorkspaceInvitation::class, function (WorkspaceInvitation $notification) use ($invitee, $path): bool {
            $mail = $notification->toMail($invitee);

            $this->assertStringContainsString($path, $mail->actionUrl);
            $this->assertStringNotContainsString('signature=', $mail->actionUrl);

            return true;
        });
    }

    public function test_invite_attaches_an_existing_user_without_resetting_their_password(): void
    {
        Notification::fake();

        $existing = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => 'secret-password',
        ]);
        $originalHash = $existing->password;

        $this->signInAs(WorkspaceRole::WorkspaceAdmin);

        $this->post('/workspace/members/invite', [
            'email' => 'existing@example.com',
            'role' => WorkspaceRole::Viewer->value,
        ])->assertRedirect();

        $existing->refresh();

        $this->assertTrue($existing->belongsToWorkspace($this->workspace));
        $this->assertSame(WorkspaceRole::Viewer, $existing->roleIn($this->workspace));
        $this->assertFalse($existing->must_set_password);
        $this->assertSame($originalHash, $existing->password);

        Notification::assertSentTo($existing, WorkspaceInvitation::class);
    }

    public function test_set_password_page_renders_for_a_pending_invite(): void
    {
        Notification::fake();

        $this->signInAs(WorkspaceRole::WorkspaceAdmin);
        $this->post('/workspace/members/invite', [
            'email' => 'join@example.com',
            'name' => 'Join User',
            'role' => WorkspaceRole::Member->value,
        ]);

        $invitation = Invitation::query()->firstOrFail();

        $this->post('/logout');

        $this->assertGuest();

        $this->get('/invitations/'.$invitation->token)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Auth/SetPassword')
                ->where('invitation.email', 'join@example.com')
                ->where('invitation.name', 'Join User')
                ->where('invitation.token', $invitation->token));
    }

    public function test_members_page_shows_a_shareable_invite_path(): void
    {
        Notification::fake();

        $this->signInAs(WorkspaceRole::WorkspaceAdmin);
        $this->post('/workspace/members/invite', [
            'email' => 'share@example.com',
            'role' => WorkspaceRole::Member->value,
        ]);

        $invitation = Invitation::query()->firstOrFail();
        $path = '/invitations/'.$invitation->token;

        $this->get('/workspace/members')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Workspace/Members')
                ->where('invites.0.invite_path', $path)
                ->where('invites.0.token', $invitation->token));
    }

    public function test_invitee_cannot_log_in_before_setting_a_password(): void
    {
        Notification::fake();

        $this->signInAs(WorkspaceRole::WorkspaceAdmin);
        $this->post('/workspace/members/invite', [
            'email' => 'locked@example.com',
            'role' => WorkspaceRole::Member->value,
        ]);

        $this->post('/logout');

        $this->from('/login')->post('/login', [
            'email' => 'locked@example.com',
            'password' => 'password',
        ])->assertRedirect('/login')->assertSessionHasErrors([
            'email' => 'Check your email to set a password.',
        ]);

        $this->assertGuest();
    }

    public function test_setting_a_password_verifies_the_account_and_scopes_projects(): void
    {
        Notification::fake();

        $foreignWorkspace = Workspace::factory()->create();
        Project::factory()->create([
            'workspace_id' => $foreignWorkspace->id,
            'name' => 'Foreign Project',
        ]);

        $this->signInAs(WorkspaceRole::WorkspaceAdmin);
        Project::factory()->create([
            'workspace_id' => $this->workspace?->id,
            'name' => 'Home Project',
        ]);

        $this->post('/workspace/members/invite', [
            'email' => 'join@example.com',
            'name' => 'Join User',
            'role' => WorkspaceRole::Member->value,
        ]);

        $invitation = Invitation::query()->firstOrFail();
        $this->post('/logout');

        $this->post('/invitations/'.$invitation->token, [
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard'));

        $invitee = User::query()->where('email', 'join@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($invitee);
        $this->assertFalse($invitee->must_set_password);
        $this->assertNotNull($invitee->email_verified_at);
        $this->assertTrue(Hash::check('password', $invitee->password));
        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->assertSame($this->workspace?->id, session('current_workspace_id'));

        $this->get('/projects')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Projects/Index')
                ->has('projects', 1)
                ->where('projects.0.name', 'Home Project'));
    }

    #[DataProvider('rolesThatCannotInvite')]
    public function test_non_admin_roles_cannot_invite(WorkspaceRole $role): void
    {
        $this->signInAs($role);

        $this->post('/workspace/members/invite', [
            'email' => 'blocked@example.com',
            'role' => WorkspaceRole::Member->value,
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'blocked@example.com']);
        $this->assertDatabaseCount('invitations', 0);
    }

    /**
     * @return array<string, array{0: WorkspaceRole}>
     */
    public static function rolesThatCannotInvite(): array
    {
        return [
            'project_manager' => [WorkspaceRole::ProjectManager],
            'member' => [WorkspaceRole::Member],
            'viewer' => [WorkspaceRole::Viewer],
        ];
    }
}
