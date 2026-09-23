<?php

namespace Tests\Feature;

use App\Models\BacklogItem;
use App\Models\BudgetItem;
use App\Models\Changelog;
use App\Models\DefinitionItem;
use App\Models\Kickoff;
use App\Models\LessonLearned;
use App\Models\Project;
use App\Models\QualityCheck;
use App\Models\Resource;
use App\Models\Risk;
use App\Models\Sprint;
use App\Models\Stakeholder;
use App\Models\Workflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageFieldModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_module_models_persist_the_detailed_page_fields(): void
    {
        $project = Project::factory()->create([
            'team' => 'Marketing Team',
            'priority' => 'high',
            'budget' => 50000,
        ]);

        $resource = Resource::factory()->create([
            'email' => 'jane@example.com',
            'availability_percent' => 80,
        ]);

        $risk = Risk::factory()->create([
            'category' => 'scope',
            'owner' => 'Jane Smith',
        ]);

        $change = Changelog::factory()->create([
            'requestor' => 'John Smith',
            'approval_status' => 'approved',
            'impact' => 'medium',
        ]);

        $lesson = LessonLearned::factory()->create([
            'description' => 'Involve stakeholders earlier.',
            'impact_sentiment' => 'positive',
        ]);

        $check = QualityCheck::factory()->create([
            'priority' => 'high',
        ]);

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'team' => 'Marketing Team', 'priority' => 'high']);
        $this->assertDatabaseHas('resources', ['id' => $resource->id, 'email' => 'jane@example.com', 'availability_percent' => 80]);
        $this->assertDatabaseHas('risks', ['id' => $risk->id, 'category' => 'scope', 'owner' => 'Jane Smith']);
        $this->assertDatabaseHas('changelogs', ['id' => $change->id, 'requestor' => 'John Smith', 'approval_status' => 'approved']);
        $this->assertDatabaseHas('lesson_learneds', ['id' => $lesson->id, 'impact_sentiment' => 'positive']);
        $this->assertDatabaseHas('quality_checks', ['id' => $check->id, 'priority' => 'high']);
    }

    public function test_initiation_and_agile_models_persist_the_page_fields(): void
    {
        $project = Project::factory()->create();

        $kickoff = Kickoff::factory()->create([
            'project_id' => $project->id,
            'attendees' => 12,
            'status' => 'completed',
        ]);

        $stakeholder = Stakeholder::factory()->create([
            'project_id' => $project->id,
            'influence' => 'high',
            'interest' => 'high',
        ]);

        $sprint = Sprint::factory()->create([
            'project_id' => $project->id,
            'name' => 'Sprint 12',
            'story_points' => 34,
            'completed_points' => 22,
        ]);

        $item = BacklogItem::factory()->create([
            'project_id' => $project->id,
            'sprint_id' => $sprint->id,
            'type' => 'epic',
            'points' => 21,
        ]);

        $workflow = Workflow::factory()->create([
            'name' => 'Feature Development',
            'stages' => ['Backlog', 'Design', 'Development', 'Done'],
        ]);

        $definition = DefinitionItem::factory()->create([
            'project_id' => $project->id,
            'kind' => 'dor',
            'is_checked' => true,
        ]);

        $budget = BudgetItem::factory()->create([
            'project_id' => $project->id,
            'category' => 'Development',
            'allocated' => 200000,
        ]);

        $this->assertDatabaseHas('kickoffs', ['id' => $kickoff->id, 'attendees' => 12, 'status' => 'completed']);
        $this->assertDatabaseHas('stakeholders', ['id' => $stakeholder->id, 'influence' => 'high']);
        $this->assertDatabaseHas('sprints', ['id' => $sprint->id, 'name' => 'Sprint 12', 'story_points' => 34]);
        $this->assertDatabaseHas('backlog_items', ['id' => $item->id, 'type' => 'epic', 'points' => 21]);
        $this->assertDatabaseHas('workflows', ['id' => $workflow->id, 'name' => 'Feature Development']);
        $this->assertDatabaseHas('definition_items', ['id' => $definition->id, 'kind' => 'dor', 'is_checked' => true]);
        $this->assertDatabaseHas('budget_items', ['id' => $budget->id, 'category' => 'Development']);
        $this->assertSame(['Backlog', 'Design', 'Development', 'Done'], $workflow->fresh()->stages);
    }
}
