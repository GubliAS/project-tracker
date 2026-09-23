<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_platform_admin || $user->workspaces()->exists();
    }

    public function view(User $user, Workspace $workspace): bool
    {
        return $user->is_platform_admin || $user->belongsToWorkspace($workspace);
    }

    public function create(User $user): bool
    {
        return $user->is_platform_admin;
    }

    public function update(User $user, Workspace $workspace): bool
    {
        return $user->is_platform_admin || $user->roleIn($workspace)?->canManageWorkspace() === true;
    }

    public function delete(User $user, Workspace $workspace): bool
    {
        return $user->is_platform_admin || $user->roleIn($workspace)?->canManageWorkspace() === true;
    }

    public function manageMembers(User $user, Workspace $workspace): bool
    {
        return $user->is_platform_admin || $user->roleIn($workspace)?->canManageMembers() === true;
    }
}
