<?php

use App\Http\Controllers\Admin\AdminAuditController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminWorkspaceController;
use App\Http\Controllers\AgileController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InitiationController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QualityController;
use App\Http\Controllers\QualityTestingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceMemberController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

require __DIR__.'/auth.php';

Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
Route::post('/invitations/{token}', [InvitationController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('invitations.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/workspace/switch', [WorkspaceController::class, 'switch'])->name('workspace.switch');

    Route::middleware('platform')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminWorkspaceController::class, 'overview'])->name('index');
        Route::get('/workspaces', [AdminWorkspaceController::class, 'index'])->name('workspaces');
        Route::post('/workspaces', [AdminWorkspaceController::class, 'store'])->name('workspaces.store');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users');
        Route::put('/users/{user}/platform-admin', [AdminUserController::class, 'togglePlatformAdmin'])->name('users.platform');
        Route::get('/audit', [AdminAuditController::class, 'index'])->name('audit');
    });

    Route::middleware('workspace')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('home');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/initiation', fn () => Inertia::render('Initiation'))->name('initiation.index');
        Route::get('/agile', fn () => Inertia::render('Agile'))->name('agile.index');

        Route::prefix('projects')->name('projects.')->group(function () {
            Route::get('/create', [ProjectController::class, 'create'])->name('create');
            Route::post('/', [ProjectController::class, 'store'])->name('store');
            Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
            Route::put('/{project}', [ProjectController::class, 'update'])->name('update');
            Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('initiation')->name('initiation.')->group(function () {
            Route::get('/kickoff', [InitiationController::class, 'kickoff'])->name('kickoff');
            Route::post('/kickoff', [InitiationController::class, 'storeKickoff'])->name('kickoff.store');
            Route::put('/kickoff/{kickoff}', [InitiationController::class, 'updateKickoff'])->name('kickoff.update');
            Route::delete('/kickoff/{kickoff}', [InitiationController::class, 'destroyKickoff'])->name('kickoff.destroy');
            Route::get('/stakeholders', [InitiationController::class, 'stakeholders'])->name('stakeholders');
            Route::post('/stakeholders', [InitiationController::class, 'storeStakeholder'])->name('stakeholders.store');
            Route::put('/stakeholders/{stakeholder}', [InitiationController::class, 'updateStakeholder'])->name('stakeholders.update');
            Route::delete('/stakeholders/{stakeholder}', [InitiationController::class, 'destroyStakeholder'])->name('stakeholders.destroy');
        });

        Route::prefix('agile')->name('agile.')->group(function () {
            Route::get('/sprints', [AgileController::class, 'sprints'])->name('sprints');
            Route::post('/sprints', [AgileController::class, 'storeSprint'])->name('sprints.store');
            Route::put('/sprints/{sprint}', [AgileController::class, 'updateSprint'])->name('sprints.update');
            Route::delete('/sprints/{sprint}', [AgileController::class, 'destroySprint'])->name('sprints.destroy');
            Route::get('/backlog', [AgileController::class, 'backlog'])->name('backlog');
            Route::post('/backlog', [AgileController::class, 'storeBacklog'])->name('backlog.store');
            Route::put('/backlog/{backlogItem}', [AgileController::class, 'updateBacklog'])->name('backlog.update');
            Route::delete('/backlog/{backlogItem}', [AgileController::class, 'destroyBacklog'])->name('backlog.destroy');
            Route::get('/definitions', [AgileController::class, 'definitions'])->name('definitions');
            Route::post('/definitions', [AgileController::class, 'storeDefinition'])->name('definitions.store');
            Route::put('/definitions/{definitionItem}', [AgileController::class, 'updateDefinition'])->name('definitions.update');
            Route::delete('/definitions/{definitionItem}', [AgileController::class, 'destroyDefinition'])->name('definitions.destroy');
        });

        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/', [TaskController::class, 'index'])->name('index');
            Route::post('/', [TaskController::class, 'store'])->name('store');
            Route::get('/kanban', [TaskController::class, 'kanban'])->name('kanban');
            Route::get('/workflows', [TaskController::class, 'workflows'])->name('workflows');
            Route::post('/workflows', [TaskController::class, 'storeWorkflow'])->name('workflows.store');
            Route::put('/workflows/{workflow}', [TaskController::class, 'updateWorkflow'])->name('workflows.update');
            Route::delete('/workflows/{workflow}', [TaskController::class, 'destroyWorkflow'])->name('workflows.destroy');
            Route::put('/{task}', [TaskController::class, 'update'])->name('update');
            Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('resources')->name('resources.')->group(function () {
            Route::get('/', fn () => redirect()->route('resources.team'))->name('index');
            Route::post('/', [ResourceController::class, 'store'])->name('store');
            Route::get('/team', [ResourceController::class, 'team'])->name('team');
            Route::get('/time-tracking', [ResourceController::class, 'timeTracking'])->name('time-tracking');
            Route::post('/time-tracking', [ResourceController::class, 'storeTime'])->name('time-tracking.store');
            Route::delete('/time-tracking/{timeEntry}', [ResourceController::class, 'destroyTime'])->name('time-tracking.destroy');
            Route::get('/budget', [ResourceController::class, 'budget'])->name('budget');
            Route::post('/budget', [ResourceController::class, 'storeBudget'])->name('budget.store');
            Route::put('/budget/{budgetItem}', [ResourceController::class, 'updateBudget'])->name('budget.update');
            Route::delete('/budget/{budgetItem}', [ResourceController::class, 'destroyBudget'])->name('budget.destroy');
            Route::get('/milestones', [ResourceController::class, 'milestones'])->name('milestones');
            Route::post('/milestones', [ResourceController::class, 'storeMilestone'])->name('milestones.store');
            Route::put('/milestones/{milestone}', [ResourceController::class, 'updateMilestone'])->name('milestones.update');
            Route::delete('/milestones/{milestone}', [ResourceController::class, 'destroyMilestone'])->name('milestones.destroy');
            Route::get('/gantt', [ResourceController::class, 'gantt'])->name('gantt');
            Route::put('/{resource}', [ResourceController::class, 'update'])->name('update');
            Route::delete('/{resource}', [ResourceController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('quality')->name('quality.')->group(function () {
            Route::get('/', fn () => redirect()->route('quality.qa-testing'))->name('index');
            Route::post('/', [QualityController::class, 'store'])->name('store');
            Route::get('/qa-testing', [QualityTestingController::class, 'index'])->name('qa-testing');
            Route::post('/qa-testing', [QualityTestingController::class, 'store'])->name('qa-testing.store');
            Route::put('/qa-testing/{qualityCheck}', [QualityTestingController::class, 'update'])->name('qa-testing.update');
            Route::delete('/qa-testing/{qualityCheck}', [QualityTestingController::class, 'destroy'])->name('qa-testing.destroy');
            Route::get('/risks', [RiskController::class, 'index'])->name('risks.index');
            Route::post('/risks', [RiskController::class, 'store'])->name('risks.store');
            Route::put('/risks/{risk}', [RiskController::class, 'update'])->name('risks.update');
            Route::delete('/risks/{risk}', [RiskController::class, 'destroy'])->name('risks.destroy');
            Route::get('/change-log', [ChangelogController::class, 'index'])->name('changelog');
            Route::post('/change-log', [ChangelogController::class, 'store'])->name('changelog.store');
            Route::put('/change-log/{changelog}', [ChangelogController::class, 'update'])->name('changelog.update');
            Route::delete('/change-log/{changelog}', [ChangelogController::class, 'destroy'])->name('changelog.destroy');
            Route::put('/{qualityCheck}', [QualityController::class, 'update'])->name('update');
            Route::delete('/{qualityCheck}', [QualityController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', fn () => redirect()->route('reports.analytics'))->name('index');
            Route::get('/analytics', [ReportController::class, 'analytics'])->name('analytics');
            Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
            Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
            Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
            Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
            Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
            Route::redirect('/lessons', '/reports/lessons-learned');
            Route::get('/lessons-learned', [LessonController::class, 'index'])->name('lessons');
            Route::post('/lessons-learned', [LessonController::class, 'store'])->name('lessons.store');
            Route::put('/lessons-learned/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
            Route::delete('/lessons-learned/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
        });

        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::post('/chat', [ChatController::class, 'store'])->name('chat.store');

        Route::get('/workspace', [WorkspaceController::class, 'show'])->name('workspace.show');
        Route::get('/workspace/settings', [WorkspaceController::class, 'settings'])->name('workspace.settings');
        Route::put('/workspace/settings', [WorkspaceController::class, 'update'])->name('workspace.settings.update');
        Route::delete('/workspace', [WorkspaceController::class, 'destroy'])->name('workspace.destroy');
        Route::get('/workspace/members', [WorkspaceMemberController::class, 'index'])->name('workspace.members');
        Route::post('/workspace/members/invite', [WorkspaceMemberController::class, 'invite'])->name('workspace.members.invite');
        Route::put('/workspace/members/{user}', [WorkspaceMemberController::class, 'update'])->name('workspace.members.update');
        Route::delete('/workspace/members/{user}', [WorkspaceMemberController::class, 'destroy'])->name('workspace.members.destroy');
        Route::get('/workspace/invites', [WorkspaceMemberController::class, 'invites'])->name('workspace.invites');
        Route::post('/workspace/invites/{invitation}/resend', [WorkspaceMemberController::class, 'resendInvite'])->name('workspace.invites.resend');
        Route::delete('/workspace/invites/{invitation}', [WorkspaceMemberController::class, 'destroyInvite'])->name('workspace.invites.destroy');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });
});
