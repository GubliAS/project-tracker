<?php

namespace App\Http\Controllers;

use App\Enums\WorkspaceRole;
use App\Http\Requests\InviteWorkspaceMemberRequest;
use App\Http\Requests\Workspace\UpdateWorkspaceMemberRequest;
use App\Models\AuditLog;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\WorkspaceInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        ]);
    }

    public function invite(InviteWorkspaceMemberRequest $request): RedirectResponse
    {
        $workspace = $this->workspace()->workspace();

        abort_unless($workspace, 403, 'No workspace selected.');

        $validated = $request->validated();
        $email = Str::lower($validated['email']);

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

        return back()->with('message', $this->inviteShareMessage($email, 'sent'));
    }

    public function update(UpdateWorkspaceMemberRequest $request, User $user): RedirectResponse
    {
        $workspace = $this->workspace()->workspace();

        abort_unless($workspace, 403, 'No workspace selected.');

        $validated = $request->validated();

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

        return back()->with('message', $this->inviteShareMessage($invitation->email, 'resent'));
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

    private function inviteShareMessage(string $email, string $verb): string
    {
        return 'Invitation '.$verb.' to '.$email.'.';
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
