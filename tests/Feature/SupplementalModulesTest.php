<?php

namespace Tests\Feature;

use App\Models\Changelog;
use App\Models\ChatMessage;
use App\Models\Document;
use App\Models\LessonLearned;
use App\Models\Project;
use App\Models\QualityCheck;
use App\Models\Risk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupplementalModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_testing_dashboard_renders_and_logs_test_runs(): void
    {
        $this->withoutVite();
        $project = Project::factory()->create();
        QualityCheck::factory()->create(['project_id' => $project->id, 'check_type' => 'testing']);

        $this->get('/quality/qa-testing')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Quality/Testing')->has('testCases', 1));

        $this->post('/quality/qa-testing', [
            'project_id' => $project->id,
            'title' => 'Checkout regression',
            'status' => 'passed',
            'notes' => 'All payment cases passed.',
        ])->assertRedirect(route('quality.qa-testing'));

        $this->assertDatabaseHas('quality_checks', ['title' => 'Checkout regression', 'check_type' => 'testing', 'status' => 'passed']);
    }

    public function test_risks_can_be_created_updated_and_deleted(): void
    {
        $project = Project::factory()->create();

        $this->post('/quality/risks', ['title' => 'Vendor delay', 'impact' => 'high', 'probability' => 'medium', 'status' => 'open', 'mitigation_plan' => 'Prepare a backup supplier.', 'project_id' => $project->id])
            ->assertRedirect(route('quality.risks.index'));

        $risk = Risk::query()->firstOrFail();
        $this->put("/quality/risks/{$risk->id}", ['title' => 'Vendor delay', 'impact' => 'high', 'probability' => 'medium', 'status' => 'mitigated', 'mitigation_plan' => 'Backup supplier approved.', 'project_id' => $project->id])
            ->assertRedirect(route('quality.risks.index'));

        $this->assertDatabaseHas('risks', ['id' => $risk->id, 'status' => 'mitigated']);
        $this->delete("/quality/risks/{$risk->id}")->assertRedirect(route('quality.risks.index'));
    }

    public function test_changelog_records_are_managed(): void
    {
        $this->post('/quality/change-log', ['version' => 'v1.2.0', 'title' => 'Risk register', 'description' => 'Added the project risk register.', 'type' => 'feature', 'release_date' => '2026-09-17'])
            ->assertRedirect(route('quality.changelog'));

        $change = Changelog::query()->firstOrFail();
        $this->put("/quality/change-log/{$change->id}", ['version' => 'v1.2.1', 'title' => 'Risk register update', 'description' => 'Improved severity labels.', 'type' => 'improvement', 'release_date' => '2026-09-18'])
            ->assertRedirect(route('quality.changelog'));

        $this->assertDatabaseHas('changelogs', ['id' => $change->id, 'version' => 'v1.2.1']);
        $this->delete("/quality/change-log/{$change->id}")->assertRedirect(route('quality.changelog'));
    }

    public function test_documents_are_uploaded_and_available_for_download(): void
    {
        Storage::fake('public');
        $project = Project::factory()->create();

        $this->post('/reports/documents', ['file' => UploadedFile::fake()->create('plan.pdf', 120, 'application/pdf'), 'category' => 'planning', 'project_id' => $project->id])
            ->assertRedirect(route('reports.documents.index'));

        $document = Document::query()->firstOrFail();
        Storage::disk('public')->assertExists($document->file_path);
        $this->get("/reports/documents/{$document->id}/preview")->assertOk();
        $this->get("/reports/documents/{$document->id}/download")->assertOk();
    }

    public function test_lessons_are_managed(): void
    {
        $project = Project::factory()->create();

        $this->post('/reports/lessons-learned', ['title' => 'Validate early', 'category' => 'Delivery', 'impact_level' => 'high', 'recommendation' => 'Confirm assumptions during kickoff.', 'project_id' => $project->id])
            ->assertRedirect(route('reports.lessons'));

        $lesson = LessonLearned::query()->firstOrFail();
        $this->put("/reports/lessons-learned/{$lesson->id}", ['title' => 'Validate early', 'category' => 'Delivery', 'impact_level' => 'medium', 'recommendation' => 'Review assumptions each sprint.', 'project_id' => $project->id])
            ->assertRedirect(route('reports.lessons'));

        $this->assertDatabaseHas('lesson_learneds', ['id' => $lesson->id, 'impact_level' => 'medium']);
        $this->delete("/reports/lessons-learned/{$lesson->id}")->assertRedirect(route('reports.lessons'));
    }

    public function test_chat_renders_project_messages_and_sends_a_message(): void
    {
        $this->withoutVite();
        $project = Project::factory()->create(['name' => 'Client Portal']);
        ChatMessage::factory()->create(['project_id' => $project->id, 'message' => 'Initial project update.']);

        $this->get("/chat?project={$project->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Chat/Index')->where('selectedProjectId', $project->id)->has('messages', 1));

        $this->post('/chat', ['project_id' => $project->id, 'message' => 'The review is ready.'])
            ->assertRedirect(route('chat.index', ['project' => $project->id]));

        $this->assertDatabaseHas('chat_messages', ['project_id' => $project->id, 'message' => 'The review is ready.']);
    }
}
