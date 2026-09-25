<?php

namespace App\Support;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthorizesWorkspace
{
    public function __construct(private WorkspaceContext $workspace) {}

    public function context(): WorkspaceContext
    {
        return $this->workspace;
    }

    public function authorizeView(): void
    {
        abort_unless($this->workspace->canAccessCurrent(), 403);
    }

    public function authorizeWriteProjects(): void
    {
        Gate::authorize('create', Project::class);
    }

    public function authorizeWriteOps(): void
    {
        abort_unless($this->workspace->canWriteOps(), 403);
    }

    public function authorizeWriteMember(): void
    {
        abort_unless($this->workspace->canWriteMemberContent(), 403);
    }

    public function authorizeManageMembers(): void
    {
        abort_unless($this->workspace->canManageMembers(), 403);
    }

    public function authorizeManageWorkspace(): void
    {
        abort_unless($this->workspace->canManageWorkspace(), 403);
    }

    public function authorizeCreateTask(): void
    {
        abort_unless($this->workspace->canWriteOps(), 403);
    }

    public function authorizeUpdateTask(Task $task, User $user): void
    {
        abort_unless($this->workspace->canWriteOps(), 403);
    }

    public function authorizeUpdateTaskStatus(Task $task, User $user): void
    {
        if ($this->workspace->canWriteOps()) {
            return;
        }

        abort_unless($this->workspace->canWriteMemberContent(), 403);
        abort_unless($task->user_id === $user->id, 403);
    }

    public function authorizeDeleteTask(): void
    {
        abort_unless($this->workspace->canWriteOps(), 403);
    }

    public function authorizeUpdateBacklog(): void
    {
        abort_unless($this->workspace->canWriteMemberContent(), 403);
    }

    public function authorizeWriteQuality(): void
    {
        abort_unless($this->workspace->canWriteMemberContent(), 403);
    }

    public function authorizeWriteDocuments(): void
    {
        abort_unless($this->workspace->canWriteMemberContent(), 403);
    }

    public function authorizeWriteLessons(): void
    {
        abort_unless($this->workspace->canWriteMemberContent(), 403);
    }

    public function authorizeWriteChat(): void
    {
        abort_unless($this->workspace->canWriteMemberContent(), 403);
    }

    public function authorizeWriteTime(): void
    {
        abort_unless($this->workspace->canWriteMemberContent(), 403);
    }

    public function ensureRecordInWorkspace(Model $model): void
    {
        $workspaceId = $model->getAttribute('workspace_id');

        if ($workspaceId) {
            abort_unless((int) $workspaceId === (int) $this->workspace->id(), 404);

            return;
        }

        $projectId = $model->getAttribute('project_id');

        if ($projectId) {
            abort_unless($this->workspace->belongsToCurrentWorkspace(projectId: (int) $projectId), 404);

            return;
        }

        if ($model instanceof Project) {
            abort_unless($this->workspace->belongsToCurrentWorkspace(workspaceId: (int) $model->workspace_id), 404);
        }
    }

    /**
     * @throws HttpException
     */
    public function ensureProjectIdInWorkspace(?int $projectId): void
    {
        if (! $projectId) {
            return;
        }

        abort_unless($this->workspace->belongsToCurrentWorkspace(projectId: $projectId), 404);
    }
}
