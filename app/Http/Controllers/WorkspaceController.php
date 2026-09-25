<?php

namespace App\Http\Controllers;

use App\Enums\Currency;
use App\Enums\WorkspaceRole;
use App\Http\Requests\Workspace\StoreWorkspaceRequest;
use App\Http\Requests\Workspace\SwitchWorkspaceRequest;
use App\Http\Requests\Workspace\UpdateWorkspaceRequest;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class WorkspaceController extends Controller
{
    public function show(): Response
    {
        $workspace = $this->workspace()->workspace();

        abort_unless($workspace, 403, 'No workspace selected.');
        $this->authorize('view', $workspace);

        $members = $workspace->users()->orderBy('name')->get();

        return $this->inertiaPage('Workspace/Index', 'Workspace', [
            'workspace' => $workspace->only(['id', 'name', 'slug']),
            'counts' => [
                'projects' => Project::query()->where('workspace_id', $workspace->id)->count(),
                'members' => $members->count(),
                'pending_invites' => $workspace->invitations()->pending()->count(),
            ],
            'members_by_role' => collect($members)
                ->groupBy(fn ($user) => (string) $user->pivot->role)
                ->map(fn ($group, $role) => [
                    'role' => $role,
                    'count' => $group->count(),
                ])
                ->values(),
            'projects' => Project::query()
                ->where('workspace_id', $workspace->id)
                ->latest()
                ->limit(8)
                ->get(['id', 'name', 'status', 'priority', 'end_date']),
            'activity' => AuditLog::query()
                ->where('workspace_id', $workspace->id)
                ->with('user:id,name')
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (AuditLog $log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'created_at' => $log->created_at?->toIso8601String(),
                    'user' => $log->user?->name,
                ]),
        ]);
    }

    public function store(StoreWorkspaceRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $validated = $request->safe()->only(['name', 'currency']);

        $workspace = DB::transaction(function () use ($user, $validated): Workspace {
            $attributes = ['name' => $validated['name']];

            if (! empty($validated['currency'])) {
                $attributes['currency'] = $validated['currency'];
            }

            $workspace = Workspace::query()->create($attributes);

            $user->workspaces()->attach($workspace->id, [
                'role' => WorkspaceRole::WorkspaceAdmin->value,
            ]);

            return $workspace;
        });

        $request->session()->put('current_workspace_id', $workspace->id);

        AuditLog::record('workspace.created', $workspace, $user, $workspace, [
            'name' => $workspace->name,
        ]);

        return redirect()->route('dashboard')->with('message', 'Workspace created.');
    }

    public function settings(): Response
    {
        $workspace = $this->workspace()->workspace();

        abort_unless($workspace, 403, 'No workspace selected.');
        $this->authorize('update', $workspace);

        return $this->inertiaPage('Workspace/Settings', 'Workspace Settings', [
            'workspace' => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'slug' => $workspace->slug,
                'currency' => $workspace->currency?->value ?? Currency::Usd->value,
            ],
            'currencies' => Currency::options(),
        ]);
    }

    public function update(UpdateWorkspaceRequest $request): RedirectResponse
    {
        $workspace = $this->workspace()->workspace();

        abort_unless($workspace, 403, 'No workspace selected.');

        $validated = $request->validated();

        $updates = [
            'name' => $validated['name'],
            'currency' => $validated['currency'],
        ];

        if ($workspace->name !== $validated['name']) {
            $updates['slug'] = Workspace::uniqueSlug($validated['name']);
        }

        $workspace->update($updates);

        AuditLog::record('workspace.updated', $workspace, $request->user(), $workspace, [
            'name' => $workspace->name,
            'currency' => $workspace->currency?->value,
        ]);

        return redirect()->route('workspace.settings')->with('message', 'Workspace updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $workspace = $this->workspace()->workspace();

        abort_unless($workspace, 403, 'No workspace selected.');
        $this->authorize('delete', $workspace);

        AuditLog::record('workspace.deleted', $workspace, $request->user(), $workspace, [
            'name' => $workspace->name,
        ]);

        $workspace->delete();
        $request->session()->forget('current_workspace_id');

        return redirect()->route('dashboard')->with('message', 'Workspace deleted.');
    }

    public function switch(SwitchWorkspaceRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $validated = $request->validated();

        $workspace = Workspace::query()->findOrFail($validated['workspace_id']);

        abort_unless($user->is_platform_admin || $user->belongsToWorkspace($workspace), 403);

        $request->session()->put('current_workspace_id', $workspace->id);

        return redirect()->route('dashboard')->with('message', 'Switched to '.$workspace->name.'.');
    }
}
