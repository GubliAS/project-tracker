<?php

namespace App\Http\Requests\Concerns;

use App\Models\Resource;
use App\Models\Sprint;
use App\Models\Task;
use App\Support\WorkspaceContext;
use Illuminate\Validation\Validator;

trait AuthorizesWorkspaceWrite
{
    protected function workspaceContext(): WorkspaceContext
    {
        return app(WorkspaceContext::class);
    }

    protected function hasCurrentWorkspace(): bool
    {
        return $this->workspaceContext()->id() !== null;
    }

    /**
     * @return list<string>
     */
    protected function projectIdRules(bool $required = false, bool $partial = false): array
    {
        $presence = match (true) {
            $partial => 'sometimes',
            $required => 'required',
            default => 'nullable',
        };

        $rules = [$presence, 'integer', 'exists:projects,id'];

        if ($partial || ! $required) {
            array_splice($rules, 1, 0, ['nullable']);
        }

        return $rules;
    }

    /**
     * @return list<callable(Validator): void>
     */
    protected function afterProjectIsInWorkspace(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('project_id') || ! $this->exists('project_id')) {
                    return;
                }

                $projectId = $this->integer('project_id');

                if (! $projectId) {
                    return;
                }

                abort_unless(
                    $this->workspaceContext()->belongsToCurrentWorkspace(projectId: $projectId),
                    404,
                );
            },
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    protected function afterRelatedIdsAreInWorkspace(): array
    {
        return [
            ...$this->afterProjectIsInWorkspace(),
            function (Validator $validator): void {
                if ($validator->errors()->has('sprint_id') || ! $this->filled('sprint_id')) {
                    return;
                }

                $sprint = Sprint::query()->find($this->integer('sprint_id'));
                abort_unless($sprint, 404);

                if ($sprint->project_id) {
                    abort_unless(
                        $this->workspaceContext()->belongsToCurrentWorkspace(projectId: (int) $sprint->project_id),
                        404,
                    );
                }
            },
            function (Validator $validator): void {
                if ($validator->errors()->has('resource_id') || ! $this->filled('resource_id')) {
                    return;
                }

                $resource = Resource::query()->find($this->integer('resource_id'));
                abort_unless(
                    $resource && (int) $resource->workspace_id === (int) $this->workspaceContext()->id(),
                    404,
                );
            },
            function (Validator $validator): void {
                if ($validator->errors()->has('task_id') || ! $this->filled('task_id')) {
                    return;
                }

                $task = Task::query()->find($this->integer('task_id'));
                abort_unless($task, 404);

                if ($task->project_id) {
                    abort_unless(
                        $this->workspaceContext()->belongsToCurrentWorkspace(projectId: (int) $task->project_id),
                        404,
                    );
                }
            },
        ];
    }
}
