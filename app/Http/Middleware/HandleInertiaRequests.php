<?php

namespace App\Http\Middleware;

use App\Enums\Currency;
use App\Models\User;
use App\Models\Workspace;
use App\Support\WorkspaceContext;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $context = app(WorkspaceContext::class);
        $current = $context->workspace();
        $workspaces = $user instanceof User
            ? ($user->is_platform_admin
                ? Workspace::query()->orderBy('name')->get(['id', 'name', 'slug'])
                : $user->workspaces()->orderBy('name')->get(['workspaces.id', 'name', 'slug']))
            : collect();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user instanceof User ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'is_platform_admin' => $user->is_platform_admin,
                ] : null,
            ],
            'currentWorkspace' => $current ? [
                'id' => $current->id,
                'name' => $current->name,
                'slug' => $current->slug,
                'role' => $context->role()?->value,
            ] : null,
            'currency' => Currency::shared($current?->currency),
            'currencies' => Currency::options(),
            'workspaces' => $workspaces,
            'canSwitchWorkspaces' => $user instanceof User && (
                $user->is_platform_admin || $workspaces->count() > 1
            ),
            'abilities' => $user instanceof User ? $context->abilities() : [
                'manage_members' => false,
                'manage_workspace' => false,
                'write_projects' => false,
                'write_ops' => false,
                'write_member' => false,
                'is_viewer' => false,
                'is_platform_admin' => false,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'status' => fn () => $request->session()->get('status'),
            ],
        ];
    }
}
