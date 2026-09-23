<?php

namespace App\Http\Controllers;

use App\Models\Risk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class RiskController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('DatabaseList', 'Risks & Issues', [
            'items' => $this->workspace()->scopeViaProject(Risk::query())->with('project:id,name')->latest()->get(),
            'fields' => [
                ['label' => 'Risk', 'path' => 'title'],
                ['label' => 'Project', 'path' => 'project.name'],
                ['label' => 'Impact', 'path' => 'impact'],
                ['label' => 'Status', 'path' => 'status'],
            ],
            'projects' => $this->workspace()->projects()->orderBy('name')->get(['id', 'name']),
            'form' => [
                'storeUrl' => '/quality/risks',
                'updateUrl' => '/quality/risks',
                'destroyUrl' => '/quality/risks',
                'createLabel' => 'Add risk',
                'fields' => [
                    ['name' => 'title', 'label' => 'Risk', 'type' => 'text', 'required' => true],
                    ['name' => 'project_id', 'label' => 'Project', 'type' => 'project'],
                    ['name' => 'impact', 'label' => 'Impact', 'type' => 'select', 'options' => ['low', 'medium', 'high'], 'required' => true],
                    ['name' => 'probability', 'label' => 'Probability', 'type' => 'select', 'options' => ['low', 'medium', 'high'], 'required' => true],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['open', 'monitoring', 'mitigated', 'closed'], 'required' => true],
                    ['name' => 'mitigation_plan', 'label' => 'Mitigation plan', 'type' => 'textarea'],
                ],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizer()->authorizeWriteQuality();
        $data = $this->validated($request);
        $this->authorizer()->ensureProjectIdInWorkspace($data['project_id'] ?? null);
        Risk::query()->create($data);

        return redirect()->route('quality.risks.index')->with('message', 'Risk created successfully.');
    }

    public function update(Request $request, Risk $risk): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($risk);
        $this->authorizer()->authorizeWriteQuality();
        $data = $this->validated($request);
        $this->authorizer()->ensureProjectIdInWorkspace($data['project_id'] ?? null);
        $risk->update($data);

        return redirect()->route('quality.risks.index')->with('message', 'Risk updated successfully.');
    }

    public function destroy(Risk $risk): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($risk);
        $this->authorizer()->authorizeWriteOps();
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
