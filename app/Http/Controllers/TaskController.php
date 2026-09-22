<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('Tasks/Index', 'Task List', [
            'tasks' => Task::query()
                ->with(['project:id,name', 'user:id,name'])
                ->latest()
                ->get(),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'in:todo,in_progress,review,done'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date'],
        ]);

        Task::query()->create($validated);

        return redirect()->route('tasks.index')->with('message', 'Task created successfully.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'project_id' => ['sometimes', 'nullable', 'exists:projects,id'],
            'user_id' => ['sometimes', 'nullable', 'exists:users,id'],
            'status' => ['sometimes', 'required', 'in:todo,in_progress,review,done'],
            'priority' => ['sometimes', 'required', 'in:low,medium,high,urgent'],
            'due_date' => ['sometimes', 'nullable', 'date'],
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')->with('message', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('message', 'Task deleted successfully.');
    }

    public function kanban(): Response
    {
        return $this->inertiaPage('Tasks/Kanban', 'Kanban Board', [
            'tasks' => Task::query()->with(['project:id,name', 'user:id,name'])->latest()->get(),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function workflows(): Response
    {
        return $this->inertiaPage('Tasks/Workflows', 'Workflows', [
            'workflows' => [
                ['id' => 1, 'name' => 'Bug Fix Workflow', 'stages' => ['Reported', 'Triaged', 'In Progress', 'Code Review', 'QA', 'Resolved'], 'projects' => 5],
                ['id' => 2, 'name' => 'Feature Development', 'stages' => ['Backlog', 'Design', 'Development', 'Testing', 'Deployment', 'Done'], 'projects' => 8],
                ['id' => 3, 'name' => 'Sprint Workflow', 'stages' => ['Sprint Backlog', 'In Progress', 'Review', 'Done'], 'projects' => 12],
            ],
        ]);
    }
}
