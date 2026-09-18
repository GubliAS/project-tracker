<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('Reports/Documents', 'Documents', [
            'documents' => Document::query()->with('project:id,name')->latest()->get(),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:20480'],
            'category' => ['required', 'in:planning,design,technical,financial,quality,other'],
            'project_id' => ['nullable', 'exists:projects,id'],
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        Document::query()->create([
            'name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'category' => $validated['category'],
            'size' => $file->getSize(),
            'project_id' => $validated['project_id'] ?? null,
        ]);

        return redirect()->route('reports.documents.index')->with('message', 'Document uploaded successfully.');
    }

    public function download(Document $document): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        return Storage::disk('public')->download($document->file_path, $document->name);
    }

    public function preview(Document $document): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        return Storage::disk('public')->response($document->file_path, $document->name, [
            'Content-Disposition' => 'inline; filename="'.$document->name.'"',
        ]);
    }

    public function destroy(Document $document): RedirectResponse
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->route('reports.documents.index')->with('message', 'Document deleted successfully.');
    }
}
