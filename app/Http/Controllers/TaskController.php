<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workflow;
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
        Task::query()->create([
            'status' => 'todo',
            ...$this->taskData($request),
        ]);

        return back()->with('message', 'Task created successfully.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $task->update($this->taskData($request, true));

        return back()->with('message', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return back()->with('message', 'Task deleted successfully.');
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
            'workflows' => Workflow::query()->latest()->get(),
        ]);
    }

    public function storeWorkflow(Request $request): RedirectResponse
    {
        Workflow::query()->create($this->workflowData($request));

        return redirect()->route('tasks.workflows')->with('message', 'Workflow created successfully.');
    }

    public function updateWorkflow(Request $request, Workflow $workflow): RedirectResponse
    {
        $workflow->update($this->workflowData($request, true));

        return redirect()->route('tasks.workflows')->with('message', 'Workflow updated successfully.');
    }

    public function destroyWorkflow(Workflow $workflow): RedirectResponse
    {
        $workflow->delete();

        return redirect()->route('tasks.workflows')->with('message', 'Workflow deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function taskData(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'title' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'project_id' => ['sometimes', 'nullable', 'exists:projects,id'],
            'user_id' => ['sometimes', 'nullable', 'exists:users,id'],
            'status' => [$partial ? 'sometimes' : 'nullable', 'in:todo,in_progress,review,done'],
            'priority' => [$partial ? 'sometimes' : 'required', 'in:low,medium,high,urgent'],
            'due_date' => ['sometimes', 'nullable', 'date'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function workflowData(Request $request, bool $partial = false): array
    {
        $validated = $request->validate([
            'name' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'stages' => [$partial ? 'sometimes' : 'required'],
        ]);

        if (array_key_exists('stages', $validated)) {
            $stages = $validated['stages'];

            if (is_string($stages)) {
                $stages = preg_split('/\s*,\s*/', $stages, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            }

            $validated['stages'] = array_values(array_filter(array_map(
                fn (mixed $stage): string => trim((string) $stage),
                is_array($stages) ? $stages : [],
            )));
        }

        return $validated;
    }
}
