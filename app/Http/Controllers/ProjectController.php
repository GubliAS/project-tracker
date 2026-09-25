<?php

namespace App\Http\Controllers;

use App\Enums\Currency;
use App\Enums\WorkspaceRole;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Document;
use App\Models\Project;
use App\Notifications\ProjectCreated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('Projects/Index', 'Projects List', [
            'projects' => $this->workspace()->projects()
                ->with(['tasks:id,project_id,status,weight'])
                ->withCount([
                    'tasks',
                    'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
                ])
                ->latest()
                ->get()
                ->map(function (Project $project): Project {
                    $project->setAttribute('progress_percent', $project->progressPercent());
                    $project->unsetRelation('tasks');

                    return $project;
                }),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Project::class);

        return $this->inertiaPage('Projects/Create', 'Create Project');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['currency'] ??= $this->workspace()->workspace()?->currency?->value ?? Currency::Usd->value;
        $documentCategory = $validated['document_category'] ?? 'other';
        $projectAttributes = collect($validated)->except(['documents', 'document_category'])->all();
        $uploaded = $validated['documents'] ?? [];

        $project = DB::transaction(function () use ($request, $projectAttributes, $documentCategory, $uploaded): Project {
            $project = Project::query()->create([
                'workspace_id' => $this->currentWorkspaceId(),
                ...$projectAttributes,
            ]);

            if ($uploaded instanceof UploadedFile) {
                $uploaded = [$uploaded];
            }

            foreach ($uploaded ?? [] as $file) {
                if ($file instanceof UploadedFile) {
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

        $workspace = $project->workspace ?? $this->workspace()->workspace();

        if ($workspace) {
            Notification::send(
                $workspace->users()->wherePivot('role', WorkspaceRole::WorkspaceAdmin->value)->get(),
                new ProjectCreated($project->loadMissing('workspace')),
            );
        }

        return redirect()->route('projects.show', $routeParameters)->with('message', 'Project created successfully.');
    }

    public function show(Project $project): Response
    {
        $this->authorizer()->ensureRecordInWorkspace($project);
        $this->authorize('view', $project);

        $project->load(['tasks.project:id,name', 'tasks.user:id,name', 'documents.user:id,name']);
        $project->setAttribute('progress_percent', $project->progressPercent());
        $members = $this->workspaceMembers();

        return $this->inertiaPage('Projects/Show', 'Project Details', [
            'project' => $project,
            'members' => $members,
            'users' => $members,
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($project);

        $project->update($request->validated());

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
