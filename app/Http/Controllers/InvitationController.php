<?php

namespace App\Http\Controllers;

use App\Enums\WorkspaceRole;
use App\Models\AuditLog;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController extends Controller
{
    public function show(Invitation $invitation): Response|RedirectResponse
    {
        abort_unless($invitation->isPending(), 403, 'This invitation is no longer valid.');

        $user = User::query()->where('email', $invitation->email)->first();

        if ($user && ! $user->must_set_password) {
            return redirect()->route('login')->with(
                'status',
                'You have been added to '.($invitation->workspace?->name ?? 'the workspace').'. Sign in to continue.',
            );
        }

        return Inertia::render('Auth/SetPassword', [
            'title' => 'Set your password',
            'invitation' => [
                'token' => $invitation->token,
                'email' => $invitation->email,
                'name' => $user?->name ?? $invitation->email,
                'role' => $invitation->role?->value ?? $invitation->role,
                'workspace' => $invitation->workspace?->only(['id', 'name']),
                'expires_at' => $invitation->expires_at,
            ],
        ]);
    }

    public function store(Request $request, Invitation $invitation): RedirectResponse
    {
        abort_unless($invitation->isPending(), 403, 'This invitation is no longer valid.');

        $user = User::query()->where('email', $invitation->email)->firstOrFail();
        $workspace = $invitation->workspace;
        abort_unless($workspace, 404);

        if ($user->must_set_password) {
            $validated = $request->validate([
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);

            $user->forceFill([
                'password' => $validated['password'],
                'must_set_password' => false,
                'email_verified_at' => now(),
            ])->save();
        }

        $workspace->users()->syncWithoutDetaching([
            $user->id => ['role' => $invitation->role instanceof WorkspaceRole
                ? $invitation->role->value
                : $invitation->role],
        ]);

        $invitation->update(['accepted_at' => now()]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('current_workspace_id', $workspace->id);

        AuditLog::record('invitation.accepted', $workspace, $user, $invitation, [
            'email' => $user->email,
        ]);

        return redirect()->route('dashboard')->with('message', 'Welcome to '.$workspace->name.'.');
    }
}
