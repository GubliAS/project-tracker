<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('Projects/Index', 'Projects List', [
            'projects' => Project::query()->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
            ])->latest()->get(),
        ]);
    }

    public function create(): Response
    {
        return $this->inertiaPage('Projects/Create', 'Create Project');
    }

    public function store(Request $request): RedirectResponse
    {
        $project = Project::query()->create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:planning,active,on_hold,completed'],
            'team' => ['nullable', 'string', 'max:255'],
            'client' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'project_type' => ['nullable', 'in:hybrid,predictive,agile'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'spent' => ['nullable', 'numeric', 'min:0'],
            'settings' => ['nullable', 'array'],
        ]));

        return redirect()->route('projects.show', $project)->with('message', 'Project created successfully.');
    }

    public function show(Project $project): Response
    {
        $project->load(['tasks.project:id,name', 'tasks.user:id,name']);

        return $this->inertiaPage('Projects/Show', 'Project Details', ['project' => $project]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $project->update($request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'in:planning,active,on_hold,completed'],
            'team' => ['nullable', 'string', 'max:255'],
            'client' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'project_type' => ['nullable', 'in:hybrid,predictive,agile'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'spent' => ['nullable', 'numeric', 'min:0'],
            'settings' => ['nullable', 'array'],
        ]));

        return redirect()->route('projects.show', $project)->with('message', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')->with('message', 'Project deleted successfully.');
    }
}
