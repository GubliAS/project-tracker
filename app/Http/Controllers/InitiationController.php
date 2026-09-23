<?php

namespace App\Http\Controllers;

use App\Models\Kickoff;
use App\Models\Stakeholder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class InitiationController extends Controller
{
    public function index(): Response
    {
        return $this->kickoff();
    }

    public function kickoff(): Response
    {
        return $this->inertiaPage('Initiation/Kickoff', 'Project Kick-Off', [
            'kickoffs' => $this->workspace()->scopeViaProject(Kickoff::query())->with('project:id,name')->latest('scheduled_on')->latest()->get(),
            'projects' => $this->workspace()->projects()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeKickoff(Request $request): RedirectResponse
    {
        $this->authorizer()->authorizeWriteOps();
        $data = $this->kickoffData($request);
        $this->authorizer()->ensureProjectIdInWorkspace($data['project_id'] ?? null);
        $data['objectives'] ??= [
            ['text' => 'Define project scope and deliverables', 'completed' => false],
            ['text' => 'Identify key stakeholders and roles', 'completed' => false],
            ['text' => 'Establish communication channels', 'completed' => false],
            ['text' => 'Set up project timeline and milestones', 'completed' => false],
        ];

        Kickoff::query()->create($data);

        return redirect()->route('initiation.kickoff')->with('message', 'Kick-off scheduled successfully.');
    }

    public function updateKickoff(Request $request, Kickoff $kickoff): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($kickoff);
        $this->authorizer()->authorizeWriteOps();
        $kickoff->update($this->kickoffData($request, true));

        return redirect()->route('initiation.kickoff')->with('message', 'Kick-off updated successfully.');
    }

    public function destroyKickoff(Kickoff $kickoff): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($kickoff);
        $this->authorizer()->authorizeWriteOps();
        $kickoff->delete();

        return redirect()->route('initiation.kickoff')->with('message', 'Kick-off deleted successfully.');
    }

    public function stakeholders(): Response
    {
        return $this->inertiaPage('Initiation/Stakeholders', 'Stakeholders', [
            'stakeholders' => $this->workspace()->scopeViaProject(Stakeholder::query())->with('project:id,name')->latest()->get(),
            'projects' => $this->workspace()->projects()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeStakeholder(Request $request): RedirectResponse
    {
        $this->authorizer()->authorizeWriteOps();
        $data = $this->stakeholderData($request);
        $this->authorizer()->ensureProjectIdInWorkspace($data['project_id'] ?? null);
        Stakeholder::query()->create($data);

        return redirect()->route('initiation.stakeholders')->with('message', 'Stakeholder added successfully.');
    }

    public function updateStakeholder(Request $request, Stakeholder $stakeholder): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($stakeholder);
        $this->authorizer()->authorizeWriteOps();
        $stakeholder->update($this->stakeholderData($request, true));

        return redirect()->route('initiation.stakeholders')->with('message', 'Stakeholder updated successfully.');
    }

    public function destroyStakeholder(Stakeholder $stakeholder): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($stakeholder);
        $this->authorizer()->authorizeWriteOps();
        $stakeholder->delete();

        return redirect()->route('initiation.stakeholders')->with('message', 'Stakeholder deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function kickoffData(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'project_id' => [$partial ? 'sometimes' : 'required', 'exists:projects,id'],
            'scheduled_on' => [$partial ? 'sometimes' : 'required', 'date'],
            'attendees' => [$partial ? 'sometimes' : 'required', 'integer', 'min:0', 'max:500'],
            'status' => [$partial ? 'sometimes' : 'required', 'in:scheduled,completed'],
            'objectives' => ['nullable', 'array'],
            'objectives.*.text' => ['required_with:objectives', 'string', 'max:255'],
            'objectives.*.completed' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function stakeholderData(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'name' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'role' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'influence' => [$partial ? 'sometimes' : 'required', 'in:low,medium,high'],
            'interest' => [$partial ? 'sometimes' : 'required', 'in:low,medium,high'],
        ]);
    }
}
