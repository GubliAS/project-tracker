<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\QualityCheck;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class QualityTestingController extends Controller
{
    public function index(): Response
    {
        $testCases = QualityCheck::query()
            ->where('check_type', 'testing')
            ->with('project:id,name')
            ->latest()
            ->get();

        return $this->inertiaPage('Quality/Testing', 'QA & Testing', [
            'testCases' => $testCases,
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'summary' => [
                'passed' => $testCases->where('status', 'passed')->count(),
                'failed' => $testCases->where('status', 'failed')->count(),
                'untested' => $testCases->where('status', 'pending')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:pending,passed,failed'],
            'notes' => ['nullable', 'string'],
        ]);

        QualityCheck::query()->create([
            ...$validated,
            'check_type' => 'testing',
        ]);

        return redirect()->route('quality.qa-testing')->with('message', 'Test run logged successfully.');
    }

    public function update(Request $request, QualityCheck $qualityCheck): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,passed,failed'],
            'notes' => ['nullable', 'string'],
        ]);

        abort_unless($qualityCheck->check_type === 'testing', 404);

        $qualityCheck->update($validated);

        return redirect()->route('quality.qa-testing')->with('message', 'Test run updated successfully.');
    }
}
