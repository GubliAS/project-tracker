<?php

namespace App\Http\Controllers;

use App\Http\Requests\Document\DestroyDocumentRequest;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('DatabaseList', 'Documents', [
            'items' => $this->workspace()->scopeViaProject(Document::query())->with('project:id,name')->latest()->get(),
            'fields' => [
                ['label' => 'Document', 'path' => 'name'],
                ['label' => 'Project', 'path' => 'project.name'],
                ['label' => 'Category', 'path' => 'category'],
                ['label' => 'Size', 'path' => 'size'],
            ],
            'projects' => $this->workspace()->projects()->orderBy('name')->get(['id', 'name']),
            'form' => [
                'storeUrl' => '/reports/documents',
                'destroyUrl' => '/reports/documents',
                'createLabel' => 'Upload document',
                'forceFormData' => true,
                'fields' => [
                    ['name' => 'file', 'label' => 'File', 'type' => 'file', 'required' => true],
                    ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'options' => ['planning', 'design', 'technical', 'financial', 'quality', 'other'], 'required' => true],
                    ['name' => 'project_id', 'label' => 'Project', 'type' => 'project'],
                ],
            ],
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $file = $validated['file'];
        $projectId = isset($validated['project_id']) ? (int) $validated['project_id'] : null;

        abort_unless($file instanceof UploadedFile, 422);

        $document = Document::storeUploaded(
            $file,
            $projectId,
            $validated['category'],
            $request->user()?->id,
        );

        if (($validated['return_to_project'] ?? false) && $document->project_id) {
            return redirect()
                ->route('projects.show', ['project' => $document->project_id, 'tab' => 'files'])
                ->with('message', 'Document uploaded successfully.');
        }

        return redirect()->route('reports.documents.index')->with('message', 'Document uploaded successfully.');
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($document);
        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        return Storage::disk('public')->download($document->file_path, $document->name);
    }

    public function preview(Document $document): StreamedResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($document);
        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        return Storage::disk('public')->response($document->file_path, $document->name, [
            'Content-Disposition' => 'inline; filename="'.$document->name.'"',
        ]);
    }

    public function destroy(DestroyDocumentRequest $request, Document $document): RedirectResponse
    {
        $this->authorizer()->ensureRecordInWorkspace($document);

        $projectId = $document->project_id;
        $validated = $request->validated();

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        if (($validated['return_to_project'] ?? false) && $projectId) {
            return redirect()
                ->route('projects.show', ['project' => $projectId, 'tab' => 'files'])
                ->with('message', 'Document deleted successfully.');
        }

        return redirect()->route('reports.documents.index')->with('message', 'Document deleted successfully.');
    }
}
