<?php

namespace Tests\Feature;

use App\Models\BacklogItem;
use App\Models\BudgetItem;
use App\Models\DefinitionItem;
use App\Models\Kickoff;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\QualityCheck;
use App\Models\Sprint;
use App\Models\Stakeholder;
use App\Models\TimeEntry;
use App\Models\Workflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    public function test_dashboard_renders_live_kpis_from_the_database(): void
    {
        $this->withoutVite();
        Project::factory()->create(['status' => 'active']);
        Project::factory()->create(['status' => 'completed']);

        $this->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('kpis.ongoing', 1)
                ->where('kpis.completed', 1)
                ->has('runningProjects')
                ->has('dailyTasks')
                ->has('summaryProjects')
                ->has('teamMembers'));
    }

    public function test_kickoffs_can_be_created_updated_and_deleted(): void
    {
        $project = Project::factory()->create();

        $this->post('/initiation/kickoff', [
            'project_id' => $project->id,
            'scheduled_on' => '2026-09-24',
            'attendees' => 8,
            'status' => 'scheduled',
        ])->assertRedirect(route('initiation.kickoff'));

        $kickoff = Kickoff::query()->firstOrFail();
        $this->assertDatabaseHas('kickoffs', ['id' => $kickoff->id, 'attendees' => 8]);

        $this->put("/initiation/kickoff/{$kickoff->id}", ['status' => 'completed'])
            ->assertRedirect(route('initiation.kickoff'));
        $this->assertDatabaseHas('kickoffs', ['id' => $kickoff->id, 'status' => 'completed']);

        $this->delete("/initiation/kickoff/{$kickoff->id}")->assertRedirect(route('initiation.kickoff'));
        $this->assertDatabaseMissing('kickoffs', ['id' => $kickoff->id]);
    }

    public function test_stakeholders_can_be_created_updated_and_deleted(): void
    {
        $project = Project::factory()->create();

        $this->post('/initiation/stakeholders', [
            'project_id' => $project->id,
            'name' => 'Ada Lovelace',
            'role' => 'Sponsor',
            'department' => 'Executive',
            'influence' => 'high',
            'interest' => 'high',
        ])->assertRedirect(route('initiation.stakeholders'));

        $stakeholder = Stakeholder::query()->firstOrFail();
        $this->put("/initiation/stakeholders/{$stakeholder->id}", [
            'name' => 'Ada Lovelace',
            'role' => 'Product Owner',
            'influence' => 'high',
            'interest' => 'medium',
        ])->assertRedirect(route('initiation.stakeholders'));

        $this->assertDatabaseHas('stakeholders', ['id' => $stakeholder->id, 'role' => 'Product Owner']);
        $this->delete("/initiation/stakeholders/{$stakeholder->id}")->assertRedirect(route('initiation.stakeholders'));
    }

    public function test_sprints_and_backlog_items_are_managed(): void
    {
        $project = Project::factory()->create();

        $this->post('/agile/sprints', [
            'project_id' => $project->id,
            'name' => 'Sprint 1',
            'goal' => 'Ship auth',
            'start_date' => '2026-09-23',
            'end_date' => '2026-10-07',
            'status' => 'active',
            'story_points' => 21,
            'completed_points' => 5,
        ])->assertRedirect(route('agile.sprints'));

        $sprint = Sprint::query()->firstOrFail();
        $this->put("/agile/sprints/{$sprint->id}", ['completed_points' => 13])
            ->assertRedirect(route('agile.sprints'));
        $this->assertDatabaseHas('sprints', ['id' => $sprint->id, 'completed_points' => 13]);

        $this->post('/agile/backlog', [
            'project_id' => $project->id,
            'sprint_id' => $sprint->id,
            'title' => 'Login form',
            'type' => 'story',
            'priority' => 'high',
            'points' => 5,
            'status' => 'ready',
        ])->assertRedirect(route('agile.backlog'));

        $item = BacklogItem::query()->firstOrFail();
        $this->put("/agile/backlog/{$item->id}", ['status' => 'in-progress'])
            ->assertRedirect(route('agile.backlog'));
        $this->assertDatabaseHas('backlog_items', ['id' => $item->id, 'status' => 'in-progress']);

        $this->delete("/agile/backlog/{$item->id}")->assertRedirect(route('agile.backlog'));
        $this->delete("/agile/sprints/{$sprint->id}")->assertRedirect(route('agile.sprints'));
    }

    public function test_definition_items_and_workflows_are_managed(): void
    {
        $this->post('/agile/definitions', [
            'kind' => 'dor',
            'text' => 'Acceptance criteria written',
        ])->assertRedirect(route('agile.definitions'));

        $item = DefinitionItem::query()->firstOrFail();
        $this->put("/agile/definitions/{$item->id}", ['is_checked' => true])
            ->assertRedirect(route('agile.definitions'));
        $this->assertDatabaseHas('definition_items', ['id' => $item->id, 'is_checked' => 1]);
        $this->delete("/agile/definitions/{$item->id}")->assertRedirect(route('agile.definitions'));

        $this->post('/tasks/workflows', [
            'name' => 'Feature Development',
            'stages' => 'Backlog, Design, Development, Done',
        ])->assertRedirect(route('tasks.workflows'));

        $workflow = Workflow::query()->firstOrFail();
        $this->assertSame(['Backlog', 'Design', 'Development', 'Done'], $workflow->stages);

        $this->put("/tasks/workflows/{$workflow->id}", [
            'name' => 'Feature Flow',
            'stages' => ['Idea', 'Build', 'Ship'],
        ])->assertRedirect(route('tasks.workflows'));
        $this->assertDatabaseHas('workflows', ['id' => $workflow->id, 'name' => 'Feature Flow']);

        $this->delete("/tasks/workflows/{$workflow->id}")->assertRedirect(route('tasks.workflows'));
    }

    public function test_budget_items_and_qa_test_runs_are_managed(): void
    {
        $project = Project::factory()->create();

        $this->post('/resources/budget', [
            'project_id' => $project->id,
            'category' => 'Development',
            'allocated' => 20000,
            'spent' => 5000,
            'status' => 'on-track',
        ])->assertRedirect(route('resources.budget'));

        $item = BudgetItem::query()->firstOrFail();
        $this->put("/resources/budget/{$item->id}", [
            'category' => 'Development',
            'allocated' => 20000,
            'spent' => 8000,
            'status' => 'on-track',
        ])->assertRedirect(route('resources.budget'));
        $this->assertDatabaseHas('budget_items', ['id' => $item->id, 'spent' => 8000]);
        $this->delete("/resources/budget/{$item->id}")->assertRedirect(route('resources.budget'));

        $check = QualityCheck::factory()->create([
            'project_id' => $project->id,
            'check_type' => 'testing',
        ]);
        $this->delete("/quality/qa-testing/{$check->id}")->assertRedirect(route('quality.qa-testing'));
        $this->assertDatabaseMissing('quality_checks', ['id' => $check->id]);
    }

    public function test_empty_payloads_fail_validation_for_new_write_endpoints(): void
    {
        $this->from('/initiation/kickoff')->post('/initiation/kickoff', [])
            ->assertRedirect('/initiation/kickoff')
            ->assertSessionHasErrors(['project_id', 'scheduled_on', 'attendees', 'status']);

        $this->from('/agile/sprints')->post('/agile/sprints', [])
            ->assertRedirect('/agile/sprints')
            ->assertSessionHasErrors(['name', 'status']);

        $this->from('/resources/budget')->post('/resources/budget', [])
            ->assertRedirect('/resources/budget')
            ->assertSessionHasErrors(['category', 'allocated', 'spent', 'status']);
    }

    public function test_time_entries_and_milestones_are_managed(): void
    {
        $project = Project::factory()->create();

        $this->post('/resources/time-tracking', [
            'project_id' => $project->id,
            'entry_date' => '2026-09-23',
            'hours' => 4.5,
            'description' => 'Build dashboard wiring',
        ])->assertRedirect(route('resources.time-tracking'));

        $entry = TimeEntry::query()->firstOrFail();
        $this->delete("/resources/time-tracking/{$entry->id}")->assertRedirect(route('resources.time-tracking'));
        $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);

        $this->post('/resources/milestones', [
            'project_id' => $project->id,
            'title' => 'MVP release',
            'due_date' => '2026-10-01',
            'status' => 'upcoming',
        ])->assertRedirect(route('resources.milestones'));

        $milestone = Milestone::query()->firstOrFail();
        $this->put("/resources/milestones/{$milestone->id}", [
            'title' => 'MVP release',
            'due_date' => '2026-10-01',
            'status' => 'completed',
        ])->assertRedirect(route('resources.milestones'));
        $this->assertDatabaseHas('milestones', ['id' => $milestone->id, 'status' => 'completed']);
        $this->delete("/resources/milestones/{$milestone->id}")->assertRedirect(route('resources.milestones'));
    }
}
