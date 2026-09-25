<?php

namespace App\Http\Controllers;

use App\Http\Requests\Resource\StoreBudgetItemRequest;
use App\Http\Requests\Resource\StoreMilestoneRequest;
use App\Http\Requests\Resource\StoreResourceRequest;
use App\Http\Requests\Resource\StoreTimeEntryRequest;
use App\Http\Requests\Resource\UpdateBudgetItemRequest;
use App\Http\Requests\Resource\UpdateMilestoneRequest;
use App\Http\Requests\Resource\UpdateResourceRequest;
use App\Models\BudgetItem;
use App\Models\Milestone;
use App\Models\Resource;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class ResourceController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('Resources/Index', 'Resources Management', [
            'resources' => $this->workspace()->scopeDirect(Resource::query())->latest()->get(),
        ]);
    }

    public function store(StoreResourceRequest $request): RedirectResponse
    {
        Resource::query()->create([
            'workspace_id' => $this->currentWorkspaceId(),
            ...$request->validated(),
        ]);

        return redirect()->route('resources.index')->with('message', 'Resource created successfully.');
    }

    public function update(UpdateResourceRequest $request, Resource $resource): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($resource);
        $resource->update($request->validated());

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

    public function storeTime(StoreTimeEntryRequest $request): RedirectResponse
    {
        TimeEntry::query()->create($request->validated());

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

    public function storeBudget(StoreBudgetItemRequest $request): RedirectResponse
    {
        BudgetItem::query()->create($request->validated());

        return redirect()->route('resources.budget')->with('message', 'Budget item created successfully.');
    }

    public function updateBudget(UpdateBudgetItemRequest $request, BudgetItem $budgetItem): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($budgetItem);
        $budgetItem->update($request->validated());

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

    public function storeMilestone(StoreMilestoneRequest $request): RedirectResponse
    {
        Milestone::query()->create($request->validated());

        return redirect()->route('resources.milestones')->with('message', 'Milestone created successfully.');
    }

    public function updateMilestone(UpdateMilestoneRequest $request, Milestone $milestone): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($milestone);
        $milestone->update($request->validated());

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
}
