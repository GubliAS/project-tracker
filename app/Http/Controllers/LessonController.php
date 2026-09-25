<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lesson\StoreLessonRequest;
use App\Http\Requests\Lesson\UpdateLessonRequest;
use App\Models\LessonLearned;
use Illuminate\Http\RedirectResponse;
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

    public function store(StoreLessonRequest $request): RedirectResponse
    {
        LessonLearned::query()->create($request->validated());

        return redirect()->route('reports.lessons')->with('message', 'Lesson recorded successfully.');
    }

    public function update(UpdateLessonRequest $request, LessonLearned $lesson): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($lesson);
        $lesson->update($request->validated());

        return redirect()->route('reports.lessons')->with('message', 'Lesson updated successfully.');
    }

    public function destroy(LessonLearned $lesson): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($lesson);
        $this->authorizer()->authorizeWriteOps();
        $lesson->delete();

        return redirect()->route('reports.lessons')->with('message', 'Lesson deleted successfully.');
    }
}
