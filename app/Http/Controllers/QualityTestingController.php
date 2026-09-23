<?php

namespace App\Http\Controllers;

use App\Models\QualityCheck;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class QualityTestingController extends Controller
{
    public function index(): Response
    {
        $testCases = $this->workspace()->scopeViaProject(QualityCheck::query())
            ->where('check_type', 'testing')
            ->with('project:id,name')
            ->latest()
            ->get();

        return $this->inertiaPage('DatabaseList', 'QA & Testing', [
            'items' => $testCases,
            'fields' => [
                ['label' => 'Test', 'path' => 'title'],
                ['label' => 'Project', 'path' => 'project.name'],
                ['label' => 'Status', 'path' => 'status'],
                ['label' => 'Notes', 'path' => 'notes'],
            ],
            'projects' => $this->workspace()->projects()->orderBy('name')->get(['id', 'name']),
            'form' => [
                'storeUrl' => '/quality/qa-testing',
                'updateUrl' => '/quality/qa-testing',
                'destroyUrl' => '/quality/qa-testing',
                'createLabel' => 'Log test run',
                'fields' => [
                    ['name' => 'title', 'label' => 'Test', 'type' => 'text', 'required' => true],
                    ['name' => 'project_id', 'label' => 'Project', 'type' => 'project'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending', 'passed', 'failed'], 'required' => true],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
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

        $this->authorizer()->authorizeWriteQuality();
        $this->authorizer()->ensureProjectIdInWorkspace($validated['project_id'] ?? null);

        QualityCheck::query()->create([
            ...$validated,
            'check_type' => 'testing',
        ]);

        return redirect()->route('quality.qa-testing')->with('message', 'Test run logged successfully.');
    }

    public function update(Request $request, QualityCheck $qualityCheck): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['required', 'in:pending,passed,failed'],
            'notes' => ['nullable', 'string'],
        ]);

        abort_unless($qualityCheck->check_type === 'testing', 404);
        $this->authorizer()->ensureRecordInWorkspace($qualityCheck);
        $this->authorizer()->authorizeWriteQuality();
        $this->authorizer()->ensureProjectIdInWorkspace($validated['project_id'] ?? null);

        $qualityCheck->update($validated);

        return redirect()->route('quality.qa-testing')->with('message', 'Test run updated successfully.');
    }

    public function destroy(QualityCheck $qualityCheck): RedirectResponse
    {
        abort_unless($qualityCheck->check_type === 'testing', 404);
        $this->authorizer()->ensureRecordInWorkspace($qualityCheck);
        $this->authorizer()->authorizeWriteOps();

        $qualityCheck->delete();

        return redirect()->route('quality.qa-testing')->with('message', 'Test run deleted successfully.');
    }
}
