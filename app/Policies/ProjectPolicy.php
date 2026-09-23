<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Support\WorkspaceContext;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_platform_admin || $user->workspaces()->exists();
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->is_platform_admin) {
            return true;
        }

        $workspace = $project->workspace;

        return $workspace !== null && $user->belongsToWorkspace($workspace);
    }

    public function create(User $user): bool
    {
        return app(WorkspaceContext::class)->canWriteProjects();
    }

    public function update(User $user, Project $project): bool
    {
        if ($user->is_platform_admin) {
            return true;
        }

        $workspace = $project->workspace;

        return $workspace !== null && $user->roleIn($workspace)?->canWriteProjects() === true;
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }
}
