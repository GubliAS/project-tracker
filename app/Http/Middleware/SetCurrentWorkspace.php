<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentWorkspace
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

        $requestedId = $request->session()->get('current_workspace_id');
        $workspace = $requestedId ? Workspace::query()->find($requestedId) : null;

        if ($workspace && $this->canUse($user, $workspace)) {
            $request->session()->put('current_workspace_id', $workspace->id);

            return $next($request);
        }

        $fallback = $user->is_platform_admin
            ? Workspace::query()->orderBy('name')->first()
            : $user->workspaces()->orderBy('name')->first();

        if ($fallback) {
            $request->session()->put('current_workspace_id', $fallback->id);
        } else {
            $request->session()->forget('current_workspace_id');
        }

        return $next($request);
    }

    private function canUse(User $user, Workspace $workspace): bool
    {
        return $user->is_platform_admin || $user->belongsToWorkspace($workspace);
    }
}
