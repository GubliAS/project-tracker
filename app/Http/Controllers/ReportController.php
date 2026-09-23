<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\QualityCheck;
use App\Models\Resource;
use App\Models\Task;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        $tasks = $this->workspace()->scopeViaProject(Task::query())->with(['project:id,name', 'user:id,name'])->get();
        $resources = $this->workspace()->scopeDirect(Resource::query())->get();
        $qualityChecks = $this->workspace()->scopeViaProject(QualityCheck::query())->get();
        $projects = $this->workspace()->projects()->withCount([
            'tasks',
            'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
        ])->orderBy('name')->get();

        $taskTotal = $tasks->count();
        $tasksByStatus = [
            'todo' => $tasks->where('status', 'todo')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'review' => $tasks->where('status', 'review')->count(),
            'done' => $tasks->where('status', 'done')->count(),
        ];

        $qualityTotal = $qualityChecks->count();
        $qualityByStatus = [
            'pending' => $qualityChecks->where('status', 'pending')->count(),
            'passed' => $qualityChecks->where('status', 'passed')->count(),
            'failed' => $qualityChecks->where('status', 'failed')->count(),
        ];

        $resourceByAvailability = [
            'available' => $resources->where('availability_status', 'available')->count(),
            'allocated' => $resources->where('availability_status', 'allocated')->count(),
            'unavailable' => $resources->where('availability_status', 'unavailable')->count(),
        ];

        $workloads = $tasks
            ->groupBy(fn (Task $task) => $task->user?->name ?? 'Unassigned')
            ->map(function ($groupedTasks, $name) {
                return [
                    'name' => $name,
                    'total' => $groupedTasks->count(),
                    'done' => $groupedTasks->where('status', 'done')->count(),
                    'in_progress' => $groupedTasks->where('status', 'in_progress')->count(),
                    'todo' => $groupedTasks->where('status', 'todo')->count(),
                    'review' => $groupedTasks->where('status', 'review')->count(),
                ];
            })
            ->values();

        return $this->inertiaPage('Reports/Index', 'Reports & Analytics', [
            'stats' => [
                'total_projects' => $projects->count(),
                'total_tasks' => $taskTotal,
                'completion_rate' => $taskTotal > 0 ? round(($tasksByStatus['done'] / $taskTotal) * 100, 1) : 0,
                'pending_quality_audits' => $qualityByStatus['pending'],
                'active_resources' => $resourceByAvailability['available'] + $resourceByAvailability['allocated'],
                'quality_pass_rate' => $qualityTotal > 0 ? round(($qualityByStatus['passed'] / $qualityTotal) * 100, 1) : 0,
            ],
            'tasksByStatus' => $tasksByStatus,
            'resourceUtilization' => [
                'total' => $resources->count(),
                ...$resourceByAvailability,
            ],
            'qualityByStatus' => $qualityByStatus,
            'projectProgress' => $projects->map(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
                'tasks_count' => $project->tasks_count,
                'completed_tasks_count' => $project->completed_tasks_count,
                'progress' => $project->tasks_count > 0
                    ? round(($project->completed_tasks_count / $project->tasks_count) * 100, 1)
                    : 0,
            ]),
            'teamWorkloads' => $workloads,
        ]);
    }

    public function analytics(): Response
    {
        return $this->index();
    }

    public function documents(): Response
    {
        return $this->inertiaPage('Reports/Documents', 'Documents');
    }

    public function lessonsLearned(): Response
    {
        return $this->inertiaPage('Reports/LessonsLearned', 'Lessons Learned');
    }
}
