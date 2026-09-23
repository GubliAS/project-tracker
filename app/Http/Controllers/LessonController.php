<?php

namespace App\Http\Controllers;

use App\Models\LessonLearned;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class LessonController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('Reports/Lessons', 'Lessons Learned', [
            'lessons' => $this->workspace()->scopeViaProject(LessonLearned::query())->with('project:id,name')->latest()->get(),
            'projects' => $this->workspace()->projects()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizer()->authorizeWriteLessons();
        $data = $this->validated($request);
        $this->authorizer()->ensureProjectIdInWorkspace($data['project_id'] ?? null);
        LessonLearned::query()->create($data);

        return redirect()->route('reports.lessons')->with('message', 'Lesson recorded successfully.');
    }

    public function update(Request $request, LessonLearned $lesson): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($lesson);
        $this->authorizer()->authorizeWriteOps();
        $lesson->update($this->validated($request));

        return redirect()->route('reports.lessons')->with('message', 'Lesson updated successfully.');
    }

    public function destroy(LessonLearned $lesson): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($lesson);
        $this->authorizer()->authorizeWriteOps();
        $lesson->delete();

        return redirect()->route('reports.lessons')->with('message', 'Lesson deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'impact_level' => ['required', 'in:low,medium,high'],
            'recommendation' => ['required', 'string'],
            'project_id' => ['nullable', 'exists:projects,id'],
        ]);
    }
}
