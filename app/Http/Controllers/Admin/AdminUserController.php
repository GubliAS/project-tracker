<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('Admin/Users', 'Users', [
            'users' => User::query()
                ->with(['workspaces:id,name'])
                ->orderBy('name')
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_platform_admin' => $user->is_platform_admin,
                    'must_set_password' => $user->must_set_password,
                    'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                    'memberships' => $user->workspaces->map(fn ($workspace) => [
                        'id' => $workspace->id,
                        'name' => $workspace->name,
                        'role' => $workspace->pivot->role,
                    ]),
                ]),
        ]);
    }

    public function togglePlatformAdmin(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->is_platform_admin, 403);

        if ($request->user()?->is($user) && $user->is_platform_admin) {
            return back()->withErrors(['user' => 'You cannot remove your own platform admin access.']);
        }

        $user->update([
            'is_platform_admin' => ! $user->is_platform_admin,
        ]);

        AuditLog::record('user.platform_admin_toggled', null, $request->user(), $user, [
            'is_platform_admin' => $user->is_platform_admin,
        ]);

        return back()->with('message', $user->is_platform_admin
            ? $user->name.' is now a platform admin.'
            : $user->name.' is no longer a platform admin.');
    }
}
