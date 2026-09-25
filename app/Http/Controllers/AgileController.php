<?php

namespace App\Http\Controllers;

use App\Http\Requests\Agile\StoreBacklogItemRequest;
use App\Http\Requests\Agile\StoreDefinitionItemRequest;
use App\Http\Requests\Agile\StoreSprintRequest;
use App\Http\Requests\Agile\UpdateBacklogItemRequest;
use App\Http\Requests\Agile\UpdateDefinitionItemRequest;
use App\Http\Requests\Agile\UpdateSprintRequest;
use App\Models\BacklogItem;
use App\Models\DefinitionItem;
use App\Models\Sprint;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class AgileController extends Controller
{
    public function index(): Response
    {
        return $this->sprints();
    }

    public function sprints(): Response
    {
        $sprints = $this->workspace()->scopeViaProject(Sprint::query())->with('project:id,name')->latest('start_date')->latest()->get();

        return $this->inertiaPage('Agile/Sprints', 'Sprints', [
            'sprints' => $sprints,
            'projects' => $this->workspace()->projects()->orderBy('name')->get(['id', 'name']),
            'currentSprint' => $sprints->firstWhere('status', 'active') ?? $sprints->first(),
        ]);
    }

    public function storeSprint(StoreSprintRequest $request): RedirectResponse
    {
        Sprint::query()->create($request->validated());

        return redirect()->route('agile.sprints')->with('message', 'Sprint created successfully.');
    }

    public function updateSprint(UpdateSprintRequest $request, Sprint $sprint): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($sprint);
        $sprint->update($request->validated());

        return redirect()->route('agile.sprints')->with('message', 'Sprint updated successfully.');
    }

    public function destroySprint(Sprint $sprint): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($sprint);
        $this->authorizer()->authorizeWriteOps();
        $sprint->delete();

        return redirect()->route('agile.sprints')->with('message', 'Sprint deleted successfully.');
    }

    public function backlog(): Response
    {
        return $this->inertiaPage('Agile/Backlog', 'Backlog', [
            'items' => $this->workspace()->scopeViaProject(BacklogItem::query())->with(['project:id,name', 'sprint:id,name'])->latest()->get(),
            'projects' => $this->workspace()->projects()->orderBy('name')->get(['id', 'name']),
            'sprints' => $this->workspace()->scopeViaProject(Sprint::query())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeBacklog(StoreBacklogItemRequest $request): RedirectResponse
    {
        BacklogItem::query()->create($request->validated());

        return redirect()->route('agile.backlog')->with('message', 'Backlog item created successfully.');
    }

    public function updateBacklog(UpdateBacklogItemRequest $request, BacklogItem $backlogItem): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($backlogItem);
        $backlogItem->update($request->validated());

        return redirect()->route('agile.backlog')->with('message', 'Backlog item updated successfully.');
    }

    public function destroyBacklog(BacklogItem $backlogItem): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($backlogItem);
        $this->authorizer()->authorizeWriteOps();
        $backlogItem->delete();

        return redirect()->route('agile.backlog')->with('message', 'Backlog item deleted successfully.');
    }

    public function definitions(): Response
    {
        $items = $this->workspace()->scopeViaProjectOrWorkspace(DefinitionItem::query())->with('project:id,name')->latest()->get();

        return $this->inertiaPage('Agile/Definitions', 'DoR / DoD', [
            'dorItems' => $items->where('kind', 'dor')->values(),
            'dodItems' => $items->where('kind', 'dod')->values(),
            'projects' => $this->workspace()->projects()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeDefinition(StoreDefinitionItemRequest $request): RedirectResponse
    {
        DefinitionItem::query()->create([
            'workspace_id' => $this->currentWorkspaceId(),
            ...$request->validated(),
        ]);

        return redirect()->route('agile.definitions')->with('message', 'Criteria added successfully.');
    }

    public function updateDefinition(UpdateDefinitionItemRequest $request, DefinitionItem $definitionItem): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($definitionItem);
        $definitionItem->update($request->validated());

        return redirect()->route('agile.definitions')->with('message', 'Criteria updated successfully.');
    }

    public function destroyDefinition(DefinitionItem $definitionItem): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($definitionItem);
        $this->authorizer()->authorizeWriteOps();
        $definitionItem->delete();

        return redirect()->route('agile.definitions')->with('message', 'Criteria deleted successfully.');
    }
}
