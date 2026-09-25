<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WorkspaceRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminWorkspaceRequest;
use App\Models\AuditLog;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class AdminWorkspaceController extends Controller
{
    public function overview(): Response
    {
        return $this->inertiaPage('Admin/Index', 'Platform', [
            'stats' => [
                'workspaces' => Workspace::query()->count(),
                'users' => User::query()->count(),
                'projects' => Project::query()->count(),
                'pending_invites' => Invitation::query()->pending()->count(),
            ],
            'workspaces' => Workspace::query()
                ->withCount(['users', 'projects'])
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn (Workspace $workspace) => $this->workspaceRow($workspace)),
            'activity' => AuditLog::query()
                ->with(['user:id,name', 'workspace:id,name'])
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (AuditLog $log) => $this->auditRow($log)),
        ]);
    }

    public function index(): Response
    {
        return $this->inertiaPage('Admin/Workspaces', 'Workspaces', [
            'workspaces' => Workspace::query()
                ->withCount(['users', 'projects'])
                ->orderBy('name')
                ->get()
                ->map(fn (Workspace $workspace) => $this->workspaceRow($workspace)),
        ]);
    }

    public function store(StoreAdminWorkspaceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $workspace = Workspace::query()->create([
            'name' => $validated['name'],
        ]);

        if ($request->user() && ! $request->user()->belongsToWorkspace($workspace)) {
            $request->user()->workspaces()->attach($workspace->id, [
                'role' => WorkspaceRole::WorkspaceAdmin->value,
            ]);
        }

        AuditLog::record('workspace.created', $workspace, $request->user(), $workspace, [
            'name' => $workspace->name,
        ]);

        return redirect()->route('admin.workspaces')->with('message', 'Workspace created.');
    }

    /**
     * @return array{id: int, name: string, slug: string, users_count: int, projects_count: int, created_at: ?string}
     */
    private function workspaceRow(Workspace $workspace): array
    {
        return [
            'id' => $workspace->id,
            'name' => $workspace->name,
            'slug' => $workspace->slug,
            'users_count' => (int) $workspace->users_count,
            'projects_count' => (int) $workspace->projects_count,
            'created_at' => $workspace->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array{id: int, action: string, created_at: ?string, user: ?string, workspace: ?string}
     */
    private function auditRow(AuditLog $log): array
    {
        return [
            'id' => $log->id,
            'action' => $log->action,
            'created_at' => $log->created_at?->toIso8601String(),
            'user' => $log->user?->name,
            'workspace' => $log->workspace?->name,
        ];
    }
}
