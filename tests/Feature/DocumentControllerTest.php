<?php

namespace Tests\Feature;

use App\Enums\WorkspaceRole;
use App\Models\Document;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_upload_redirects_to_login(): void
    {
        $this->post('/reports/documents', [
            'file' => $this->fakeDocument('charter.pdf'),
            'category' => 'planning',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_member_can_attach_a_document_to_a_workspace_project(): void
    {
        Storage::fake('public');
        $user = $this->signInAs(WorkspaceRole::Member);
        $project = Project::factory()->create(['workspace_id' => $this->workspace?->id]);

        $this->post('/reports/documents', [
            'file' => $this->fakeDocument('charter.pdf'),
            'category' => 'planning',
            'project_id' => $project->id,
            'return_to_project' => true,
        ])->assertRedirect(route('projects.show', ['project' => $project, 'tab' => 'files']));

        $document = Document::query()->firstOrFail();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'name' => 'charter.pdf',
            'category' => 'planning',
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);
        Storage::disk('public')->assertExists($document->file_path);
    }

    public function test_viewer_cannot_upload_a_document(): void
    {
        Storage::fake('public');
        $this->signInAs(WorkspaceRole::Viewer);
        $project = Project::factory()->create(['workspace_id' => $this->workspace?->id]);

        $this->post('/reports/documents', [
            'file' => $this->fakeDocument('charter.pdf'),
            'category' => 'planning',
            'project_id' => $project->id,
        ])->assertForbidden();

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_upload_for_a_foreign_project_returns_404(): void
    {
        Storage::fake('public');
        $this->signInAs(WorkspaceRole::Member);
        $foreignWorkspace = Workspace::factory()->create();
        $foreignProject = Project::factory()->create(['workspace_id' => $foreignWorkspace->id]);

        $this->post('/reports/documents', [
            'file' => $this->fakeDocument('charter.pdf'),
            'category' => 'planning',
            'project_id' => $foreignProject->id,
        ])->assertNotFound();

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_empty_payload_fails_validation_and_does_not_store_a_document(): void
    {
        $this->signIn();

        $this->from('/reports/documents')
            ->post('/reports/documents', [])
            ->assertRedirect('/reports/documents')
            ->assertSessionHasErrors(['file', 'category']);

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_rejected_file_type_names_the_file_instead_of_the_field_key(): void
    {
        Storage::fake('public');
        $this->signIn();

        $this->from('/reports/documents')
            ->post('/reports/documents', [
                'file' => UploadedFile::fake()->create('notes.exe', 20),
                'category' => 'planning',
            ])
            ->assertRedirect('/reports/documents')
            ->assertSessionHasErrors([
                'file' => 'notes.exe is not an allowed file type. Use PDF, Word, Excel, PowerPoint, images, text, CSV, or zip.',
            ]);

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_creating_a_project_stores_attached_documents(): void
    {
        Storage::fake('public');
        $user = $this->signIn();

        $response = $this->post('/projects', [
            'name' => 'Client Portal',
            'status' => 'planning',
            'document_category' => 'technical',
            'documents' => [
                $this->fakeDocument('spec.pdf'),
            ],
        ]);

        $project = Project::query()->firstOrFail();
        $document = Document::query()->firstOrFail();

        $response->assertRedirect(route('projects.show', ['project' => $project, 'tab' => 'files']));

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'name' => 'spec.pdf',
            'category' => 'technical',
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);
        Storage::disk('public')->assertExists($document->file_path);
    }

    public function test_project_show_includes_attached_documents(): void
    {
        $this->withoutVite();
        $this->signIn();
        $project = Project::factory()->create(['workspace_id' => $this->workspace?->id]);
        Document::factory()->create([
            'project_id' => $project->id,
            'name' => 'kickoff.pdf',
        ]);

        $this->get("/projects/{$project->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Projects/Show')
                ->has('project.documents', 1)
                ->where('project.documents.0.name', 'kickoff.pdf'));
    }

    private function fakeDocument(string $name = 'plan.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF",
        );
    }
}
