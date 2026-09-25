<?php

namespace App\Http\Controllers;

use App\Http\Requests\Initiation\StoreKickoffRequest;
use App\Http\Requests\Initiation\StoreStakeholderRequest;
use App\Http\Requests\Initiation\UpdateKickoffRequest;
use App\Http\Requests\Initiation\UpdateStakeholderRequest;
use App\Models\Kickoff;
use App\Models\Stakeholder;
use Illuminate\Http\RedirectResponse;
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

    public function storeKickoff(StoreKickoffRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['objectives'] ??= [
            ['text' => 'Define project scope and deliverables', 'completed' => false],
            ['text' => 'Identify key stakeholders and roles', 'completed' => false],
            ['text' => 'Establish communication channels', 'completed' => false],
            ['text' => 'Set up project timeline and milestones', 'completed' => false],
        ];

        Kickoff::query()->create($data);

        return redirect()->route('initiation.kickoff')->with('message', 'Kick-off scheduled successfully.');
    }

    public function updateKickoff(UpdateKickoffRequest $request, Kickoff $kickoff): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($kickoff);
        $kickoff->update($request->validated());

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

    public function storeStakeholder(StoreStakeholderRequest $request): RedirectResponse
    {
        Stakeholder::query()->create($request->validated());

        return redirect()->route('initiation.stakeholders')->with('message', 'Stakeholder added successfully.');
    }

    public function updateStakeholder(UpdateStakeholderRequest $request, Stakeholder $stakeholder): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($stakeholder);
        $stakeholder->update($request->validated());

        return redirect()->route('initiation.stakeholders')->with('message', 'Stakeholder updated successfully.');
    }

    public function destroyStakeholder(Stakeholder $stakeholder): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($stakeholder);
        $this->authorizer()->authorizeWriteOps();
        $stakeholder->delete();

        return redirect()->route('initiation.stakeholders')->with('message', 'Stakeholder deleted successfully.');
    }
}
