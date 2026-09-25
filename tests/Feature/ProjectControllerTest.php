<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\WorkspaceRole;
use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_project_creation_to_login(): void
    {
        $this->post('/projects', [
            'name' => 'Client Portal',
            'status' => 'planning',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_unverified_users_are_redirected_from_project_creation(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post('/projects', [
                'name' => 'Client Portal',
                'status' => 'planning',
            ])
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_valid_payload_creates_a_project_and_redirects_to_show(): void
    {
        $this->signIn();

        $response = $this->post('/projects', [
            'name' => 'Client Portal',
            'description' => 'Rebuild the client portal',
            'status' => 'planning',
            'team' => 'Development Team',
            'client' => 'Acme Corp',
            'priority' => 'high',
            'project_type' => 'agile',
            'start_date' => '2026-09-23',
            'end_date' => '2026-12-23',
            'budget' => 50000,
            'settings' => [
                'sprintDuration' => '2',
                'velocity' => '20',
            ],
        ]);

        $project = Project::query()->firstOrFail();

        $response->assertRedirectToRoute('projects.show', $project)
            ->assertSessionHas('message');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Client Portal',
            'status' => 'planning',
            'team' => 'Development Team',
            'client' => 'Acme Corp',
            'priority' => 'high',
            'project_type' => 'agile',
        ]);
        $this->assertSame([
            'sprintDuration' => '2',
            'velocity' => '20',
        ], $project->settings);
        $this->assertSame(Currency::Usd, $project->currency);
    }

    public function test_created_project_uses_submitted_currency_instead_of_workspace_default(): void
    {
        $workspace = Workspace::factory()->create(['currency' => Currency::Ghs]);
        $this->signIn(null, $workspace);

        $this->post('/projects', [
            'name' => 'Cedi Then Euro',
            'status' => 'planning',
            'currency' => Currency::Eur->value,
        ])->assertRedirect();

        $project = Project::query()->firstOrFail();

        $this->assertSame(Currency::Eur, $project->currency);
        $this->assertSame(Currency::Ghs, $workspace->fresh()->currency);
    }

    public function test_created_project_defaults_to_workspace_currency_when_omitted(): void
    {
        $workspace = Workspace::factory()->create(['currency' => Currency::Ghs]);
        $this->signIn(null, $workspace);

        $this->post('/projects', [
            'name' => 'Workspace Default',
            'status' => 'planning',
        ])->assertRedirect();

        $this->assertSame(Currency::Ghs, Project::query()->firstOrFail()->currency);
    }

    public function test_create_project_page_defaults_shared_currency_to_workspace(): void
    {
        $workspace = Workspace::factory()->create(['currency' => Currency::Ghs]);
        $this->signIn(null, $workspace);

        $this->get(route('projects.create'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Projects/Create')
                ->where('currency.code', 'GHS')
                ->where('currency.symbol', 'GH₵')
                ->has('currencies'));
    }

    public function test_creating_a_project_with_an_uploaded_file_stores_the_document(): void
    {
        Storage::fake('public');
        $user = $this->signIn();

        $response = $this->post('/projects', [
            'name' => 'Client Portal',
            'status' => 'planning',
            'document_category' => 'planning',
            'documents' => [
                UploadedFile::fake()->createWithContent(
                    'charter.pdf',
                    "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF",
                ),
            ],
        ]);

        $project = Project::query()->firstOrFail();
        $document = Document::query()->firstOrFail();

        $response->assertRedirect(route('projects.show', ['project' => $project, 'tab' => 'files']))
            ->assertSessionHas('message');

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'name' => 'charter.pdf',
            'category' => 'planning',
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);
        Storage::disk('public')->assertExists($document->file_path);
    }

    public function test_creating_a_project_stores_an_uploaded_svg(): void
    {
        Storage::fake('public');
        $user = $this->signIn();

        $response = $this->post('/projects', [
            'name' => 'Client Portal',
            'status' => 'planning',
            'document_category' => 'design',
            'documents' => [
                UploadedFile::fake()->createWithContent(
                    'icon.svg',
                    '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"></svg>',
                ),
            ],
        ]);

        $project = Project::query()->firstOrFail();
        $document = Document::query()->firstOrFail();

        $response->assertRedirect(route('projects.show', ['project' => $project, 'tab' => 'files']));

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'name' => 'icon.svg',
            'category' => 'design',
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);
        Storage::disk('public')->assertExists($document->file_path);
    }

    public function test_rejected_document_type_names_the_file_instead_of_the_array_index(): void
    {
        Storage::fake('public');
        $this->signIn();

        $this->from('/projects/create')
            ->post('/projects', [
                'name' => 'Client Portal',
                'status' => 'planning',
                'documents' => [
                    UploadedFile::fake()->create('notes.exe', 20),
                ],
            ])
            ->assertRedirect('/projects/create')
            ->assertSessionHasErrors([
                'documents.0' => 'notes.exe is not an allowed file type. Use PDF, Word, Excel, PowerPoint, images, text, CSV, or zip.',
            ]);

        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_empty_payload_fails_validation_and_does_not_create_a_project(): void
    {
        $this->signIn();

        $this->from('/projects/create')
            ->post('/projects', [])
            ->assertRedirect('/projects/create')
            ->assertSessionHasErrors(['name', 'status']);

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_valid_payload_updates_and_deletes_a_project(): void
    {
        $this->signIn();

        $project = Project::factory()->create(['name' => 'Client Portal', 'status' => 'planning']);

        $this->put("/projects/{$project->id}", [
            'name' => 'Client Portal Rebuild',
            'status' => 'active',
            'priority' => 'high',
        ])->assertRedirectToRoute('projects.show', $project);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Client Portal Rebuild',
            'status' => 'active',
        ]);

        $this->delete("/projects/{$project->id}")
            ->assertRedirectToRoute('projects.index');

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_authorized_user_can_delete_a_project_from_the_destroy_route(): void
    {
        $this->signIn();
        $project = Project::factory()->create(['name' => 'Doomed Portal']);

        $this->delete(route('projects.destroy', $project))
            ->assertRedirectToRoute('projects.index')
            ->assertSessionHas('message');

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_member_cannot_delete_a_project(): void
    {
        $this->signInAs(WorkspaceRole::Member);
        $project = Project::factory()->create(['name' => 'Locked']);

        $this->delete(route('projects.destroy', $project))->assertForbidden();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Locked',
        ]);
    }
}
