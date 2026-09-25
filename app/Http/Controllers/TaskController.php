<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\StoreWorkflowRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\UpdateWorkflowRequest;
use App\Models\Task;
use App\Models\User;
use App\Models\Workflow;
use App\Notifications\TaskAssigned;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('Tasks/Index', 'Task List', $this->taskPageData());
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $data = $this->normalizedTaskData($request->validated());

        $task = Task::query()->create([
            'status' => 'todo',
            ...$data,
        ]);

        $this->notifyAssignee($task);

        return back()->with('message', 'Task created successfully.');
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($task);

        $previousAssigneeId = $task->user_id;
        $data = $this->normalizedTaskData($request->validated());

        $task->update($data);

        if (array_key_exists('user_id', $data) && $data['user_id'] !== $previousAssigneeId) {
            $this->notifyAssignee($task->fresh(['project']));
        }

        return back()->with('message', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($task);
        $this->authorize('delete', $task);
        $task->delete();

        return back()->with('message', 'Task deleted successfully.');
    }

    public function kanban(): Response
    {
        return $this->inertiaPage('Tasks/Kanban', 'Kanban Board', $this->taskPageData());
    }

    public function workflows(): Response
    {
        return $this->inertiaPage('Tasks/Workflows', 'Workflows', [
            'workflows' => $this->workspace()->scopeDirect(Workflow::query())->latest()->get(),
        ]);
    }

    public function storeWorkflow(StoreWorkflowRequest $request): RedirectResponse
    {
        Workflow::query()->create([
            'workspace_id' => $this->currentWorkspaceId(),
            ...$request->validated(),
        ]);

        return redirect()->route('tasks.workflows')->with('message', 'Workflow created successfully.');
    }

    public function updateWorkflow(UpdateWorkflowRequest $request, Workflow $workflow): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($workflow);
        $workflow->update($request->validated());

        return redirect()->route('tasks.workflows')->with('message', 'Workflow updated successfully.');
    }

    public function destroyWorkflow(Workflow $workflow): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($workflow);
        $this->authorizer()->authorizeWriteOps();
        $workflow->delete();

        return redirect()->route('tasks.workflows')->with('message', 'Workflow deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function taskPageData(): array
    {
        $members = $this->workspaceMembers();

        return [
            'tasks' => $this->workspace()->scopeViaProject(Task::query())
                ->with(['project:id,name', 'user:id,name'])
                ->latest()
                ->get(),
            'projects' => $this->workspace()->projects()->orderBy('name')->get(['id', 'name']),
            'users' => $members,
            'members' => $members,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizedTaskData(array $validated): array
    {
        if (array_key_exists('weight', $validated) && $validated['weight'] === null) {
            $validated['weight'] = 1;
        }

        return $validated;
    }

    private function notifyAssignee(Task $task): void
    {
        if (! $task->user_id) {
            return;
        }

        $assignee = $task->relationLoaded('user') ? $task->user : User::query()->find($task->user_id);

        $assignee?->notify(new TaskAssigned($task->loadMissing('project')));
    }
}
