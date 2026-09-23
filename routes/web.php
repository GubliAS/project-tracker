<?php

use App\Http\Controllers\AgileController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InitiationController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QualityController;
use App\Http\Controllers\QualityTestingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Dashboard'))->name('home');
Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/initiation', fn () => Inertia::render('Initiation'))->name('initiation.index');
Route::get('/agile', fn () => Inertia::render('Agile'))->name('agile.index');

Route::prefix('projects')->name('projects.')->group(function () {
    Route::get('/create', [ProjectController::class, 'create'])->name('create');
    Route::post('/', [ProjectController::class, 'store'])->name('store');
    Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
});

Route::prefix('initiation')->name('initiation.')->group(function () {
    Route::get('/kickoff', [InitiationController::class, 'kickoff'])->name('kickoff');
    Route::get('/stakeholders', [InitiationController::class, 'stakeholders'])->name('stakeholders');
});

Route::prefix('agile')->name('agile.')->group(function () {
    Route::get('/sprints', [AgileController::class, 'sprints'])->name('sprints');
    Route::get('/backlog', [AgileController::class, 'backlog'])->name('backlog');
    Route::get('/definitions', [AgileController::class, 'definitions'])->name('definitions');
});

Route::prefix('tasks')->name('tasks.')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('index');
    Route::post('/', [TaskController::class, 'store'])->name('store');
    Route::get('/kanban', [TaskController::class, 'kanban'])->name('kanban');
    Route::get('/workflows', [TaskController::class, 'workflows'])->name('workflows');
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
