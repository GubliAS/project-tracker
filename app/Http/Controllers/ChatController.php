<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chat\StoreChatMessageRequest;
use App\Models\ChatMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ChatController extends Controller
{
    public function index(Request $request): Response
    {
        $projects = $this->workspace()->projects()->orderBy('name')->get(['id', 'name']);
        $requestedProjectId = $request->integer('project');
        $selectedProjectId = $projects->contains('id', $requestedProjectId)
            ? $requestedProjectId
            : $projects->first()?->id;

        return $this->inertiaPage('Chat/Index', 'Project Chat', [
            'projects' => $projects,
            'selectedProjectId' => $selectedProjectId,
            'messages' => $selectedProjectId
                ? ChatMessage::query()
                    ->with('user:id,name')
                    ->where('project_id', $selectedProjectId)
                    ->oldest()
                    ->get()
                : collect(),
        ]);
    }

    public function store(StoreChatMessageRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        ChatMessage::query()->create([
            ...$validated,
            'user_id' => $request->user()?->id,
        ]);

        return redirect()->route('chat.index', ['project' => $validated['project_id']]);
    }
}
