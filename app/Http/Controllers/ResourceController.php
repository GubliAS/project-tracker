<?php

namespace App\Http\Controllers;

use App\Models\BudgetItem;
use App\Models\Milestone;
use App\Models\Resource;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ResourceController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('Resources/Index', 'Resources Management', [
            'resources' => $this->workspace()->scopeDirect(Resource::query())->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizer()->authorizeWriteOps();
        abort_unless($this->currentWorkspaceId(), 403);

        Resource::query()->create([
            'workspace_id' => $this->currentWorkspaceId(),
            ...$this->resourceData($request),
        ]);

        return redirect()->route('resources.index')->with('message', 'Resource created successfully.');
    }

    public function update(Request $request, Resource $resource): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($resource);
        $this->authorizer()->authorizeWriteOps();
        $resource->update($this->resourceData($request, true));

        return redirect()->route('resources.index')->with('message', 'Resource updated successfully.');
    }

    public function destroy(Resource $resource): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($resource);
        $this->authorizer()->authorizeWriteOps();
        $resource->delete();

        return redirect()->route('resources.index')->with('message', 'Resource deleted successfully.');
    }

    public function team(): Response
    {
        return $this->inertiaPage('Resources/Team', 'Team Resources', [
            'resources' => $this->workspace()->scopeDirect(Resource::query())->where('type', 'human')->latest()->get(),
        ]);
    }

    public function timeTracking(): Response
    {
        return $this->inertiaPage('Resources/TimeTracking', 'Time Tracking', [
            'entries' => $this->workspace()->scopeViaProject(TimeEntry::query())->with(['project:id,name', 'resource:id,name', 'task:id,title'])->latest('entry_date')->get(),
            'projects' => $this->workspace()->projects()->orderBy('name')->get(['id', 'name']),
            'resources' => $this->workspace()->scopeDirect(Resource::query())->where('type', 'human')->orderBy('name')->get(['id', 'name']),
            'tasks' => $this->workspace()->scopeViaProject(Task::query())->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function storeTime(Request $request): RedirectResponse
    {
        $this->authorizer()->authorizeWriteTime();
        $data = $this->timeData($request);
        $this->authorizer()->ensureProjectIdInWorkspace($data['project_id'] ?? null);
        TimeEntry::query()->create($data);

        return redirect()->route('resources.time-tracking')->with('message', 'Time entry added successfully.');
    }

    public function destroyTime(TimeEntry $timeEntry): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($timeEntry);
        $this->authorizer()->authorizeWriteOps();
        $timeEntry->delete();

        return redirect()->route('resources.time-tracking')->with('message', 'Time entry deleted successfully.');
    }

    public function budget(): Response
    {
        $items = $this->workspace()->scopeViaProject(BudgetItem::query())->with('project:id,name,currency')->latest()->get();
        $allocated = (float) $items->sum('allocated');
        $spent = (float) $items->sum('spent');

        return $this->inertiaPage('Resources/Budget', 'Budget', [
            'items' => $items,
            'projects' => $this->workspace()->projects()->orderBy('name')->get(['id', 'name', 'currency']),
            'summary' => [
                'total_budget' => $allocated,
                'spent' => $spent,
                'remaining' => $allocated - $spent,
                'projected' => $spent,
            ],
        ]);
    }

    public function storeBudget(Request $request): RedirectResponse
    {
        $this->authorizer()->authorizeWriteOps();
        $data = $this->budgetData($request);
        $this->authorizer()->ensureProjectIdInWorkspace($data['project_id'] ?? null);
        BudgetItem::query()->create($data);

        return redirect()->route('resources.budget')->with('message', 'Budget item created successfully.');
    }

    public function updateBudget(Request $request, BudgetItem $budgetItem): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($budgetItem);
        $this->authorizer()->authorizeWriteOps();
        $data = $this->budgetData($request, true);
        $this->authorizer()->ensureProjectIdInWorkspace($data['project_id'] ?? null);
        $budgetItem->update($data);

        return redirect()->route('resources.budget')->with('message', 'Budget item updated successfully.');
    }

    public function destroyBudget(BudgetItem $budgetItem): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($budgetItem);
        $this->authorizer()->authorizeWriteOps();
        $budgetItem->delete();

        return redirect()->route('resources.budget')->with('message', 'Budget item deleted successfully.');
    }

    public function milestones(): Response
    {
        return $this->inertiaPage('Resources/Milestones', 'Milestones', [
            'milestones' => $this->workspace()->scopeViaProject(Milestone::query())->with('project:id,name')->orderBy('due_date')->get(),
            'projects' => $this->workspace()->projects()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeMilestone(Request $request): RedirectResponse
    {
        $this->authorizer()->authorizeWriteOps();
        $data = $this->milestoneData($request);
        $this->authorizer()->ensureProjectIdInWorkspace($data['project_id'] ?? null);
        Milestone::query()->create($data);

        return redirect()->route('resources.milestones')->with('message', 'Milestone created successfully.');
    }

    public function updateMilestone(Request $request, Milestone $milestone): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($milestone);
        $this->authorizer()->authorizeWriteOps();
        $data = $this->milestoneData($request);
        $this->authorizer()->ensureProjectIdInWorkspace($data['project_id'] ?? null);
        $milestone->update($data);

        return redirect()->route('resources.milestones')->with('message', 'Milestone updated successfully.');
    }

    public function destroyMilestone(Milestone $milestone): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($milestone);
        $this->authorizer()->authorizeWriteOps();
        $milestone->delete();

        return redirect()->route('resources.milestones')->with('message', 'Milestone deleted successfully.');
    }

    public function gantt(): Response
    {
        return $this->inertiaPage('Resources/Gantt', 'Gantt Chart', [
            'tasks' => $this->workspace()->scopeViaProject(Task::query())->with('project:id,name')->whereNotNull('due_date')->orderBy('due_date')->get(),
            'milestones' => $this->workspace()->scopeViaProject(Milestone::query())->with('project:id,name')->whereNotNull('due_date')->orderBy('due_date')->get(),
        ]);
    }

    private function resourceData(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'name' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'type' => [$partial ? 'sometimes' : 'required', 'in:human,hardware,software,material'],
            'role_or_category' => ['nullable', 'string', 'max:255'],
            'cost_per_hour' => [$partial ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'availability_status' => [$partial ? 'sometimes' : 'required', 'in:available,allocated,unavailable'],
            'availability_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function budgetData(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'category' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'allocated' => [$partial ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'spent' => [$partial ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'status' => [$partial ? 'sometimes' : 'required', 'in:on-track,under,over'],
        ]);
    }

    private function timeData(Request $request): array
    {
        return $request->validate(['project_id' => ['nullable', 'exists:projects,id'], 'resource_id' => ['nullable', 'exists:resources,id'], 'task_id' => ['nullable', 'exists:tasks,id'], 'entry_date' => ['required', 'date'], 'hours' => ['required', 'numeric', 'min:0.25', 'max:24'], 'description' => ['nullable', 'string']]);
    }

    private function milestoneData(Request $request): array
    {
        return $request->validate(['project_id' => ['nullable', 'exists:projects,id'], 'title' => ['required', 'string', 'max:255'], 'due_date' => ['nullable', 'date'], 'status' => ['required', 'in:upcoming,in_progress,completed,delayed']]);
    }
}
