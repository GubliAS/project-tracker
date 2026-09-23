<?php

namespace App\Http\Controllers;

use App\Models\BacklogItem;
use App\Models\DefinitionItem;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class AgileController extends Controller
{
    public function index(): Response
    {
        return $this->sprints();
    }

    public function sprints(): Response
    {
        $sprints = Sprint::query()->with('project:id,name')->latest('start_date')->latest()->get();

        return $this->inertiaPage('Agile/Sprints', 'Sprints', [
            'sprints' => $sprints,
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'currentSprint' => $sprints->firstWhere('status', 'active') ?? $sprints->first(),
        ]);
    }

    public function storeSprint(Request $request): RedirectResponse
    {
        Sprint::query()->create($this->sprintData($request));

        return redirect()->route('agile.sprints')->with('message', 'Sprint created successfully.');
    }

    public function updateSprint(Request $request, Sprint $sprint): RedirectResponse
    {
        $sprint->update($this->sprintData($request, true));

        return redirect()->route('agile.sprints')->with('message', 'Sprint updated successfully.');
    }

    public function destroySprint(Sprint $sprint): RedirectResponse
    {
        $sprint->delete();

        return redirect()->route('agile.sprints')->with('message', 'Sprint deleted successfully.');
    }

    public function backlog(): Response
    {
        return $this->inertiaPage('Agile/Backlog', 'Backlog', [
            'items' => BacklogItem::query()->with(['project:id,name', 'sprint:id,name'])->latest()->get(),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'sprints' => Sprint::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeBacklog(Request $request): RedirectResponse
    {
        BacklogItem::query()->create($this->backlogData($request));

        return redirect()->route('agile.backlog')->with('message', 'Backlog item created successfully.');
    }

    public function updateBacklog(Request $request, BacklogItem $backlogItem): RedirectResponse
    {
        $backlogItem->update($this->backlogData($request, true));

        return redirect()->route('agile.backlog')->with('message', 'Backlog item updated successfully.');
    }

    public function destroyBacklog(BacklogItem $backlogItem): RedirectResponse
    {
        $backlogItem->delete();

        return redirect()->route('agile.backlog')->with('message', 'Backlog item deleted successfully.');
    }

    public function definitions(): Response
    {
        $items = DefinitionItem::query()->with('project:id,name')->latest()->get();

        return $this->inertiaPage('Agile/Definitions', 'DoR / DoD', [
            'dorItems' => $items->where('kind', 'dor')->values(),
            'dodItems' => $items->where('kind', 'dod')->values(),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeDefinition(Request $request): RedirectResponse
    {
        DefinitionItem::query()->create($this->definitionData($request));

        return redirect()->route('agile.definitions')->with('message', 'Criteria added successfully.');
    }

    public function updateDefinition(Request $request, DefinitionItem $definitionItem): RedirectResponse
    {
        $definitionItem->update($this->definitionData($request, true));

        return redirect()->route('agile.definitions')->with('message', 'Criteria updated successfully.');
    }

    public function destroyDefinition(DefinitionItem $definitionItem): RedirectResponse
    {
        $definitionItem->delete();

        return redirect()->route('agile.definitions')->with('message', 'Criteria deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function sprintData(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'name' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'goal' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => [$partial ? 'sometimes' : 'required', 'in:planned,active,completed'],
            'story_points' => ['nullable', 'integer', 'min:0'],
            'completed_points' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function backlogData(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'sprint_id' => ['nullable', 'exists:sprints,id'],
            'title' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'type' => [$partial ? 'sometimes' : 'required', 'in:epic,feature,story'],
            'priority' => [$partial ? 'sometimes' : 'required', 'in:low,medium,high'],
            'points' => ['nullable', 'integer', 'min:0'],
            'status' => [$partial ? 'sometimes' : 'required', 'in:backlog,ready,in-progress,done'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function definitionData(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'kind' => [$partial ? 'sometimes' : 'required', 'in:dor,dod'],
            'text' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'is_checked' => ['sometimes', 'boolean'],
        ]);
    }
}
