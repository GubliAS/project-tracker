<?php

namespace App\Support;

use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WorkspaceContext
{
    public function user(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }

    public function id(): ?int
    {
        $id = session('current_workspace_id');

        return $id ? (int) $id : null;
    }

    public function workspace(): ?Workspace
    {
        $id = $this->id();

        return $id ? Workspace::query()->find($id) : null;
    }

    public function role(?Workspace $workspace = null): ?WorkspaceRole
    {
        $user = $this->user();
        $workspace ??= $this->workspace();

        if (! $user || ! $workspace) {
            return null;
        }

        return $user->roleIn($workspace);
    }

    public function isPlatformAdmin(): bool
    {
        return (bool) $this->user()?->is_platform_admin;
    }

    public function belongsToCurrent(): bool
    {
        $user = $this->user();
        $workspace = $this->workspace();

        return $user !== null && $workspace !== null && $user->belongsToWorkspace($workspace);
    }

    public function canAccessCurrent(): bool
    {
        return $this->isPlatformAdmin() || $this->belongsToCurrent();
    }

    public function canManageMembers(): bool
    {
        return $this->isPlatformAdmin() || ($this->role()?->canManageMembers() ?? false);
    }

    public function canManageWorkspace(): bool
    {
        return $this->isPlatformAdmin() || ($this->role()?->canManageWorkspace() ?? false);
    }

    public function canWriteProjects(): bool
    {
        return $this->isPlatformAdmin() || ($this->role()?->canWriteProjects() ?? false);
    }

    public function canWriteOps(): bool
    {
        return $this->isPlatformAdmin() || ($this->role()?->canWriteOps() ?? false);
    }

    public function canWriteMemberContent(): bool
    {
        return $this->isPlatformAdmin() || ($this->role()?->canWriteMemberContent() ?? false);
    }

    public function isViewer(): bool
    {
        return ! $this->isPlatformAdmin() && ($this->role()?->isReadOnly() ?? false);
    }

    /**
     * @return array<string, bool>
     */
    public function abilities(): array
    {
        return [
            'manage_members' => $this->canManageMembers(),
            'manage_workspace' => $this->canManageWorkspace(),
            'write_projects' => $this->canWriteProjects(),
            'write_ops' => $this->canWriteOps(),
            'write_member' => $this->canWriteMemberContent(),
            'write_task_details' => $this->canWriteOps(),
            'is_viewer' => $this->isViewer(),
            'is_platform_admin' => $this->isPlatformAdmin(),
        ];
    }

    /**
     * @return Builder<Project>
     */
    public function projects(): Builder
    {
        return Project::query()->where('workspace_id', $this->id() ?? 0);
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function scopeDirect(Builder $query, string $column = 'workspace_id'): Builder
    {
        return $query->where($column, $this->id() ?? 0);
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function scopeViaProject(Builder $query, string $relation = 'project'): Builder
    {
        $workspaceId = $this->id() ?? 0;

        return $query->whereHas($relation, fn (Builder $project) => $project->where('workspace_id', $workspaceId));
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function scopeViaProjectOrWorkspace(Builder $query, string $relation = 'project'): Builder
    {
        $workspaceId = $this->id() ?? 0;

        return $query->where(function (Builder $inner) use ($relation, $workspaceId): void {
            $inner->where('workspace_id', $workspaceId)
                ->orWhereHas($relation, fn (Builder $project) => $project->where('workspace_id', $workspaceId));
        });
    }

    public function belongsToCurrentWorkspace(?int $workspaceId = null, ?int $projectId = null): bool
    {
        $currentId = $this->id();

        if ($workspaceId) {
            return $currentId === $workspaceId;
        }

        if ($projectId) {
            return Project::query()->whereKey($projectId)->where('workspace_id', $currentId)->exists();
        }

        return false;
    }
}
