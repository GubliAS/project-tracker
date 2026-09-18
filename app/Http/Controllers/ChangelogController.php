<?php

namespace App\Http\Controllers;

use App\Models\Changelog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ChangelogController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('Quality/ChangeLog', 'Change Log', [
            'changes' => Changelog::query()->orderByDesc('release_date')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Changelog::query()->create($this->validated($request));

        return redirect()->route('quality.changelog')->with('message', 'Change record created successfully.');
    }

    public function update(Request $request, Changelog $changelog): RedirectResponse
    {
        $changelog->update($this->validated($request));

        return redirect()->route('quality.changelog')->with('message', 'Change record updated successfully.');
    }

    public function destroy(Changelog $changelog): RedirectResponse
    {
        $changelog->delete();

        return redirect()->route('quality.changelog')->with('message', 'Change record deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'version' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', 'in:feature,improvement,fix,security'],
            'release_date' => ['required', 'date'],
        ]);
    }
}
