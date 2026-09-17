<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ChatController extends Controller
{
    public function index(Request $request): Response
    {
        $projects = Project::query()->orderBy('name')->get(['id', 'name']);
        $selectedProjectId = $request->integer('project') ?: $projects->first()?->id;

        return $this->inertiaPage('Chat/Index', 'Project Chat', [
            'projects' => $projects,
            'selectedProjectId' => $selectedProjectId,
            'messages' => ChatMessage::query()
                ->with('user:id,name')
                ->when($selectedProjectId, fn ($query) => $query->where('project_id', $selectedProjectId))
                ->oldest()
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ChatMessage::query()->create([
            ...$validated,
            'user_id' => $request->user()?->id,
        ]);

        return redirect()->route('chat.index', ['project' => $validated['project_id']]);
    }
}
