<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Risk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class RiskController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('Quality/Risks', 'Risks & Issues', [
            'risks' => Risk::query()->with('project:id,name')->latest()->get(),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Risk::query()->create($this->validated($request));

        return redirect()->route('quality.risks.index')->with('message', 'Risk created successfully.');
    }

    public function update(Request $request, Risk $risk): RedirectResponse
    {
        $risk->update($this->validated($request));

        return redirect()->route('quality.risks.index')->with('message', 'Risk updated successfully.');
    }

    public function destroy(Risk $risk): RedirectResponse
    {
        $risk->delete();

        return redirect()->route('quality.risks.index')->with('message', 'Risk deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'impact' => ['required', 'in:low,medium,high'],
            'probability' => ['required', 'in:low,medium,high'],
            'status' => ['required', 'in:open,monitoring,mitigated,closed'],
            'mitigation_plan' => ['nullable', 'string'],
            'project_id' => ['nullable', 'exists:projects,id'],
        ]);
    }
}
