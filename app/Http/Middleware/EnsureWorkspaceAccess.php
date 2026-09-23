<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\WorkspaceContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        if ($user->is_platform_admin) {
            return $next($request);
        }

        if ($request->routeIs('workspace.switch', 'invitations.*', 'profile.*', 'password.update')) {
            return $next($request);
        }

        $workspace = app(WorkspaceContext::class)->workspace();

        if (! $workspace) {
            return $next($request);
        }

        abort_unless($user->belongsToWorkspace($workspace), 403, 'You do not have access to this workspace.');

        return $next($request);
    }
}
