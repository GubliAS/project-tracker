<?php

namespace App\Http\Controllers;

use App\Enums\WorkspaceRole;
use App\Models\AuditLog;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\WorkspaceInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Response;

class WorkspaceMemberController extends Controller
{
    public function index(): Response
    {
        $workspace = $this->workspace()->workspace();

        abort_unless($workspace, 403, 'No workspace selected.');
        $this->authorize('view', $workspace);

        return $this->inertiaPage('Workspace/Members', 'Workspace Members', [
            'workspace' => $workspace,
            'members' => $workspace->users()->orderBy('name')->get()->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->pivot->role,
                'is_platform_admin' => $user->is_platform_admin,
                'must_set_password' => $user->must_set_password,
            ]),
            'invites' => $this->pendingInvites($workspace),
            'roles' => $this->roleOptions(),
            'app_url' => $this->appUrl(),
        ]);
    }

    public function invites(): Response
    {
        $workspace = $this->workspace()->workspace();

        abort_unless($workspace, 403, 'No workspace selected.');
        $this->authorize('manageMembers', $workspace);

        return $this->inertiaPage('Workspace/Invites', 'Pending Invites', [
            'workspace' => $workspace,
            'invites' => $this->pendingInvites($workspace),
            'roles' => $this->roleOptions(),
            'app_url' => $this->appUrl(),
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $workspace = $this->workspace()->workspace();

        abort_unless($workspace, 403, 'No workspace selected.');
        $this->authorize('manageMembers', $workspace);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::enum(WorkspaceRole::class)],
        ]);

        $email = Str::lower($validated['email']);

        if ($workspace->users()->where('users.email', $email)->exists()) {
            return back()->withErrors(['email' => 'That user is already a member of this workspace.']);
        }

        $invitation = DB::transaction(function () use ($request, $workspace, $validated, $email): Invitation {
            $user = User::query()->where('email', $email)->first();

            $name = $validated['name'] ?? null;

            if (! $user) {
                $user = User::query()->create([
                    'name' => $name ?: Str::before($email, '@'),
                    'email' => $email,
                    'password' => Str::password(32),
                    'email_verified_at' => null,
                    'must_set_password' => true,
                ]);
            } elseif ($user->must_set_password && filled($name)) {
                $user->update(['name' => $name]);
            }

            $workspace->users()->syncWithoutDetaching([
                $user->id => ['role' => $validated['role']],
            ]);

            $invitation = Invitation::query()->updateOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'email' => $email,
                    'accepted_at' => null,
                ],
                [
                    'role' => $validated['role'],
                    'token' => Str::random(48),
                    'invited_by' => $request->user()?->id,
                    'expires_at' => now()->addDays(7),
                    'accepted_at' => $user->must_set_password ? null : now(),
                ],
            );

            AuditLog::record('invitation.created', $workspace, $request->user(), $invitation, [
                'email' => $email,
                'role' => $validated['role'],
            ]);

            return $invitation->load('workspace');
        });

        $invitee = User::query()->where('email', $email)->firstOrFail();
        $invitee->notify(new WorkspaceInvitation($invitation));

        return back()->with('message', $this->inviteShareMessage($email, $invitation, 'sent'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $workspace = $this->workspace()->workspace();

        abort_unless($workspace, 403, 'No workspace selected.');
        $this->authorize('manageMembers', $workspace);

        $validated = $request->validate([
            'role' => ['required', Rule::enum(WorkspaceRole::class)],
        ]);

        abort_unless($user->belongsToWorkspace($workspace), 404);

        $workspace->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        AuditLog::record('member.role_changed', $workspace, $request->user(), $user, [
            'role' => $validated['role'],
        ]);

        return back()->with('message', 'Member role updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $workspace = $this->workspace()->workspace();

        abort_unless($workspace, 403, 'No workspace selected.');
        $this->authorize('manageMembers', $workspace);
        abort_unless($user->belongsToWorkspace($workspace), 404);

        $workspace->users()->detach($user->id);

        AuditLog::record('member.removed', $workspace, $request->user(), $user, [
            'email' => $user->email,
        ]);

        return back()->with('message', 'Member removed.');
    }

    public function destroyInvite(Request $request, Invitation $invitation): RedirectResponse
    {
        $workspace = $this->workspace()->workspace();

        abort_unless($workspace, 403, 'No workspace selected.');
        $this->authorize('manageMembers', $workspace);
        abort_unless($invitation->workspace_id === $workspace->id, 404);

        $invitation->delete();

        AuditLog::record('invitation.revoked', $workspace, $request->user(), $invitation, [
            'email' => $invitation->email,
        ]);

        return back()->with('message', 'Invitation revoked.');
    }

    public function resendInvite(Request $request, Invitation $invitation): RedirectResponse
    {
        $workspace = $this->workspace()->workspace();

        abort_unless($workspace, 403, 'No workspace selected.');
        $this->authorize('manageMembers', $workspace);
        abort_unless($invitation->workspace_id === $workspace->id, 404);

        $invitation->update([
            'expires_at' => now()->addDays(7),
        ]);

        $invitee = User::query()->where('email', $invitation->email)->first();

        if ($invitee) {
            $invitee->notify(new WorkspaceInvitation($invitation->fresh(['workspace'])));
        }

        AuditLog::record('invitation.resent', $workspace, $request->user(), $invitation, [
            'email' => $invitation->email,
        ]);

        return back()->with('message', $this->inviteShareMessage($invitation->email, $invitation, 'resent'));
    }

    /**
     * @return list<array{id: int, email: string, name: ?string, role: string, token: string, invite_path: string, invite_url: string, expires_at: mixed, must_set_password: bool}>
     */
    private function pendingInvites(Workspace $workspace): array
    {
        $invites = $workspace->invitations()
            ->pending()
            ->with('inviter:id,name,email')
            ->latest()
            ->get();

        if ($invites->isEmpty()) {
            return [];
        }

        $invitees = User::query()
            ->whereIn('email', $invites->pluck('email'))
            ->get(['email', 'name', 'must_set_password'])
            ->keyBy('email');

        return $invites
            ->map(fn (Invitation $invite) => [
                'id' => $invite->id,
                'email' => $invite->email,
                'name' => $invitees->get($invite->email)?->name,
                'role' => $invite->role?->value ?? $invite->role,
                'token' => $invite->token,
                'invite_path' => $invite->invitePath(),
                'invite_url' => $invite->inviteUrl(),
                'expires_at' => $invite->expires_at,
                'must_set_password' => (bool) $invitees->get($invite->email)?->must_set_password,
            ])
            ->all();
    }

    private function appUrl(): string
    {
        return rtrim((string) config('app.url'), '/');
    }

    private function inviteShareMessage(string $email, Invitation $invitation, string $verb): string
    {
        $path = $invitation->invitePath();
        $appUrl = $this->appUrl();

        return 'Invitation '.$verb.' to '.$email.'. Share '.$path.'. The email link uses APP_URL ('.$appUrl.'). If that host is localhost, the partner must open the path on their own running app (e.g. http://THEIR-IP:8000'.$path.') or use a shared APP_URL.';
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function roleOptions(): array
    {
        return array_map(fn (WorkspaceRole $role) => [
            'value' => $role->value,
            'label' => $role->label(),
        ], WorkspaceRole::cases());
    }
}
