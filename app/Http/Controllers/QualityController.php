<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\QualityCheck;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class QualityController extends Controller
{
    public function index(): Response
    {
        $checks = QualityCheck::query()
            ->with('project:id,name')
            ->latest()
            ->get();

        return $this->inertiaPage('Quality/Index', 'Quality Control', [
            'qualityChecks' => $checks,
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'summary' => [
                'pending' => $checks->where('status', 'pending')->count(),
                'passed' => $checks->where('status', 'passed')->count(),
                'failed' => $checks->where('status', 'failed')->count(),
                'total' => $checks->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'check_type' => ['required', 'in:code_review,testing,security_audit,compliance'],
            'status' => ['required', 'in:pending,passed,failed'],
            'notes' => ['nullable', 'string'],
        ]);

        QualityCheck::query()->create($validated);

        return redirect()->route('quality.index')->with('message', 'Quality check logged successfully.');
    }

    public function update(Request $request, QualityCheck $qualityCheck): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['sometimes', 'nullable', 'exists:projects,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'check_type' => ['sometimes', 'required', 'in:code_review,testing,security_audit,compliance'],
            'status' => ['sometimes', 'required', 'in:pending,passed,failed'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        $qualityCheck->update($validated);

        return redirect()->route('quality.index')->with('message', 'Quality check updated successfully.');
    }

    public function destroy(QualityCheck $qualityCheck): RedirectResponse
    {
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
