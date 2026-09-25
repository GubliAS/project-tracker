<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quality\StoreChangelogRequest;
use App\Http\Requests\Quality\UpdateChangelogRequest;
use App\Models\Changelog;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class ChangelogController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('DatabaseList', 'Change Log', [
            'items' => $this->workspace()->scopeDirect(Changelog::query())->orderByDesc('release_date')->latest()->get(),
            'fields' => [
                ['label' => 'Version', 'path' => 'version'],
                ['label' => 'Title', 'path' => 'title'],
                ['label' => 'Type', 'path' => 'type'],
                ['label' => 'Released', 'path' => 'release_date'],
            ],
            'form' => [
                'storeUrl' => '/quality/change-log',
                'updateUrl' => '/quality/change-log',
                'destroyUrl' => '/quality/change-log',
                'createLabel' => 'Add change',
                'fields' => [
                    ['name' => 'version', 'label' => 'Version', 'type' => 'text', 'required' => true],
                    ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
                    ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => ['feature', 'improvement', 'fix', 'security'], 'required' => true],
                    ['name' => 'release_date', 'label' => 'Release date', 'type' => 'date', 'required' => true],
                    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => true],
                ],
            ],
        ]);
    }

    public function store(StoreChangelogRequest $request): RedirectResponse
    {
        Changelog::query()->create([
            'workspace_id' => $this->currentWorkspaceId(),
            ...$request->validated(),
        ]);

        return redirect()->route('quality.changelog')->with('message', 'Change record created successfully.');
    }

    public function update(UpdateChangelogRequest $request, Changelog $changelog): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($changelog);
        $changelog->update($request->validated());

        return redirect()->route('quality.changelog')->with('message', 'Change record updated successfully.');
    }

    public function destroy(Changelog $changelog): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($changelog);
        $this->authorizer()->authorizeWriteOps();
        $changelog->delete();

        return redirect()->route('quality.changelog')->with('message', 'Change record deleted successfully.');
    }
}
