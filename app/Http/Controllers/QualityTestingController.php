<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quality\StoreQualityTestRequest;
use App\Http\Requests\Quality\UpdateQualityTestRequest;
use App\Models\QualityCheck;
use Illuminate\Http\RedirectResponse;
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

    public function store(StoreQualityTestRequest $request): RedirectResponse
    {
        QualityCheck::query()->create([
            ...$request->validated(),
            'check_type' => 'testing',
        ]);

        return redirect()->route('quality.qa-testing')->with('message', 'Test run logged successfully.');
    }

    public function update(UpdateQualityTestRequest $request, QualityCheck $qualityCheck): RedirectResponse
    {
        abort_unless($qualityCheck->check_type === 'testing', 404);
        $this->authorizer()->ensureRecordInWorkspace($qualityCheck);
        $qualityCheck->update($request->validated());

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
