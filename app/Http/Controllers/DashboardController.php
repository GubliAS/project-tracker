<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Resource;
use App\Models\Task;
use App\Models\User;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $projects = Project::query()
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
            ])
            ->latest()
            ->get();

        $tasks = Task::query()
            ->with(['project:id,name', 'user:id,name'])
            ->latest()
            ->get();

        $users = User::query()
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
            ])
            ->orderBy('name')
            ->get();

        $taskTotal = $tasks->count();
        $taskDone = $tasks->where('status', 'done')->count();

        return $this->inertiaPage('Dashboard', 'Dashboard', [
            'kpis' => [
                'new_projects' => $projects->where('status', 'planning')->count(),
                'completed' => $projects->where('status', 'completed')->count(),
                'ongoing' => $projects->where('status', 'active')->count(),
                'pending' => $projects->where('status', 'on_hold')->count(),
                'total_projects' => $projects->count(),
                'total_budget' => (float) $projects->sum('budget'),
                'task_completion_rate' => $taskTotal > 0 ? (int) round(($taskDone / $taskTotal) * 100) : 0,
            ],
            'runningProjects' => $projects
                ->whereIn('status', ['active', 'planning'])
                ->take(3)
                ->values()
                ->map(fn (Project $project) => $this->runningProject($project)),
            'dailyTasks' => $tasks->take(3)->values()->map(fn (Task $task) => $this->dailyTask($task)),
            'summaryProjects' => $projects->take(5)->values()->map(fn (Project $project, int $index) => $this->summaryProject($project, $index)),
            'teamMembers' => $users->take(5)->values()->map(fn (User $user) => $this->teamMember($user)),
            'resourceCount' => Resource::query()->where('type', 'human')->count(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function runningProject(Project $project): array
    {
        $progress = $project->tasks_count > 0
            ? (int) round(($project->completed_tasks_count / $project->tasks_count) * 100)
            : 0;

        return [
            'id' => $project->id,
            'title' => $project->name,
            'description' => $project->description ?: 'No description yet.',
            'statusLabel' => $progress.'% completed',
            'statusClass' => $progress >= 60 ? 'text-success' : ($progress >= 30 ? 'text-warning' : 'text-primary'),
            'time' => $project->updated_at?->diffForHumans() ?? '—',
            'progress' => $progress,
            'trackClass' => 'bg-primary/10',
            'barClass' => $progress >= 60 ? '' : 'bg-primarytint1color',
            'avatars' => ['/assets/img/11.jpg', '/assets/img/2.jpg', '/assets/img/5.jpg'],
            'extra' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dailyTask(Task $task): array
    {
        $accents = [
            'todo' => ['pm-accent-primary', 'ri-checkbox-blank-circle-line', 'text-primary bg-primary/10'],
            'in_progress' => ['pm-accent-tint1', 'ri-loader-4-line', 'text-primarytint1color bg-primarytint1color/10'],
            'review' => ['pm-accent-tint2', 'ri-eye-line', 'text-primarytint2color bg-primarytint2color/10'],
            'done' => ['pm-accent-primary', 'ri-check-double-line', 'text-success bg-success/10'],
        ];
        [$accent, $icon, $iconClass] = $accents[$task->status] ?? $accents['todo'];

        return [
            'id' => $task->id,
            'time' => $task->due_date?->format('M j') ?? $task->created_at?->format('h:i A') ?? '—',
            'title' => $task->title,
            'accent' => $accent,
            'icon' => $icon,
            'iconClass' => $iconClass,
            'badges' => array_values(array_filter([
                $task->project?->name ? ['label' => $task->project->name, 'class' => 'bg-primary/10 text-primary'] : null,
                ['label' => str_replace('_', ' ', $task->status), 'class' => 'bg-secondary/10 text-secondary'],
                ['label' => $task->priority, 'class' => 'bg-info/10 text-info'],
            ])),
            'avatars' => ['/assets/img/2.jpg', '/assets/img/12.jpg'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function summaryProject(Project $project, int $index): array
    {
        $progress = $project->tasks_count > 0
            ? (int) round(($project->completed_tasks_count / $project->tasks_count) * 100)
            : 0;

        $statusClasses = [
            'planning' => 'bg-warning/10 text-warning',
            'active' => 'bg-primary/10 text-primary',
            'on_hold' => 'bg-danger/10 text-danger',
            'completed' => 'bg-success/10 text-success',
        ];

        return [
            'id' => $project->id,
            'no' => $index + 1,
            'title' => $project->name,
            'tasks' => $project->completed_tasks_count,
            'tasksTotal' => $project->tasks_count,
            'progress' => $progress,
            'status' => str_replace('_', ' ', $project->status),
            'statusClass' => $statusClasses[$project->status] ?? 'bg-primary/10 text-primary',
            'due' => $project->end_date?->format('d-m-Y') ?? '—',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function teamMember(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->email,
            'works' => $user->tasks_count,
            'status' => $user->email_verified_at ? 'Online' : 'Offline',
            'tasks' => $user->completed_tasks_count,
            'tasksTotal' => $user->tasks_count,
            'avatar' => '/assets/img/2.jpg',
        ];
    }
}
