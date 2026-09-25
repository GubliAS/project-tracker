<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Support\WorkspaceContext;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return app(WorkspaceContext::class)->canAccessCurrent();
    }

    public function view(User $user, Task $task): bool
    {
        return app(WorkspaceContext::class)->canAccessCurrent();
    }

    public function create(User $user): bool
    {
        return app(WorkspaceContext::class)->canWriteOps();
    }

    public function update(User $user, Task $task): bool
    {
        return app(WorkspaceContext::class)->canWriteOps();
    }

    public function updateStatus(User $user, Task $task): bool
    {
        if (app(WorkspaceContext::class)->canWriteOps()) {
            return true;
        }

        return app(WorkspaceContext::class)->canWriteMemberContent()
            && $task->user_id === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return app(WorkspaceContext::class)->canWriteOps();
    }
}
