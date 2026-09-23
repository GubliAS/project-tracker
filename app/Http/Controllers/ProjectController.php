<?php

namespace App\Http\Controllers;

use App\Enums\Currency;
use App\Models\Document;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('Projects/Index', 'Projects List', [
            'projects' => $this->workspace()->projects()->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
            ])->latest()->get(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Project::class);

        return $this->inertiaPage('Projects/Create', 'Create Project');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Project::class);
        abort_unless($this->currentWorkspaceId(), 403, 'No workspace selected.');

        $validated = $request->validate([
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
            'currency' => ['nullable', 'string', Rule::enum(Currency::class)],
            'settings' => ['nullable', 'array'],
            'documents' => ['nullable', 'array'],
            'documents.*' => Document::uploadRules(required: false),
            'document_category' => Document::categoryRules(required: false),
        ]);

        $validated['currency'] ??= $this->workspace()->workspace()?->currency?->value ?? Currency::Usd->value;
        $documentCategory = $validated['document_category'] ?? 'other';
        $projectAttributes = collect($validated)->except(['documents', 'document_category'])->all();

        $project = DB::transaction(function () use ($request, $projectAttributes, $documentCategory): Project {
            $project = Project::query()->create([
                'workspace_id' => $this->currentWorkspaceId(),
                ...$projectAttributes,
            ]);

            $uploaded = $request->file('documents', []);

            if ($uploaded instanceof UploadedFile) {
                $uploaded = [$uploaded];
            }

            foreach ($uploaded ?? [] as $file) {
                if ($file) {
                    Document::storeUploaded(
                        $file,
                        $project->id,
                        $documentCategory,
                        $request->user()?->id,
                    );
                }
            }

            return $project;
        });

        $routeParameters = $project->documents()->exists()
            ? ['project' => $project, 'tab' => 'files']
            : $project;

        return redirect()->route('projects.show', $routeParameters)->with('message', 'Project created successfully.');
    }

    public function show(Project $project): Response
    {
        $this->authorizer()->ensureRecordInWorkspace($project);
        $this->authorize('view', $project);

        $project->load(['tasks.project:id,name', 'tasks.user:id,name', 'documents.user:id,name']);

        return $this->inertiaPage('Projects/Show', 'Project Details', ['project' => $project]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($project);
        $this->authorize('update', $project);

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
            'currency' => ['nullable', 'string', Rule::enum(Currency::class)],
            'settings' => ['nullable', 'array'],
        ]));

        return redirect()->route('projects.show', $project)->with('message', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($project);
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')->with('message', 'Project deleted successfully.');
    }
}
