<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quality\StoreQualityCheckRequest;
use App\Http\Requests\Quality\UpdateQualityCheckRequest;
use App\Models\QualityCheck;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class QualityController extends Controller
{
    public function index(): Response
    {
        $checks = $this->workspace()->scopeViaProject(QualityCheck::query())
            ->with('project:id,name')
            ->latest()
            ->get();

        return $this->inertiaPage('Quality/Index', 'Quality Control', [
            'qualityChecks' => $checks,
            'projects' => $this->workspace()->projects()->orderBy('name')->get(['id', 'name']),
            'summary' => [
                'pending' => $checks->where('status', 'pending')->count(),
                'passed' => $checks->where('status', 'passed')->count(),
                'failed' => $checks->where('status', 'failed')->count(),
                'total' => $checks->count(),
            ],
        ]);
    }

    public function store(StoreQualityCheckRequest $request): RedirectResponse
    {
        QualityCheck::query()->create($request->validated());

        return redirect()->route('quality.index')->with('message', 'Quality check logged successfully.');
    }

    public function update(UpdateQualityCheckRequest $request, QualityCheck $qualityCheck): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($qualityCheck);
        $qualityCheck->update($request->validated());

        return redirect()->route('quality.index')->with('message', 'Quality check updated successfully.');
    }

    public function destroy(QualityCheck $qualityCheck): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($qualityCheck);
        $this->authorizer()->authorizeWriteOps();
        $qualityCheck->delete();

        return redirect()->route('quality.index')->with('message', 'Quality check deleted successfully.');
    }

    public function qaTesting(): Response
    {
        return $this->inertiaPage('Quality/QaTesting', 'QA & Testing');
    }

    public function risks(): Response
    {
        return $this->inertiaPage('Quality/Risks', 'Risks & Issues');
    }

    public function changeLog(): Response
    {
        return $this->inertiaPage('Quality/ChangeLog', 'Change Log');
    }
}
