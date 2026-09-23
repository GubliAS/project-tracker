<?php

namespace App\Http\Controllers;

use App\Enums\Currency;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Response;

class SettingsController extends Controller
{
    public function edit(): Response
    {
        $workspace = $this->workspace()->workspace();

        abort_unless($workspace, 403, 'No workspace selected.');
        $this->authorize('view', $workspace);

        return $this->inertiaPage('Settings/Index', 'Settings', [
            'workspace' => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'currency' => $workspace->currency?->value ?? Currency::Usd->value,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $workspace = $this->workspace()->workspace();

        abort_unless($workspace, 403, 'No workspace selected.');
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'currency' => ['required', 'string', Rule::enum(Currency::class)],
        ]);

        $workspace->update([
            'currency' => $validated['currency'],
        ]);

        AuditLog::record('workspace.updated', $workspace, $request->user(), $workspace, [
            'currency' => $workspace->currency?->value,
        ]);

        return redirect()->route('settings')->with('message', 'Settings saved.');
    }
}
