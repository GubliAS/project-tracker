<?php

namespace Tests\Feature;

use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TaskWeightTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_defaults_weight_to_one(): void
    {
        $task = Task::factory()->create();

        $this->assertSame(1, $task->weight);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'weight' => 1,
        ]);
    }

    #[DataProvider('invalidWeights')]
    public function test_store_rejects_weight_outside_one_to_eight(int $weight): void
    {
        $this->signIn();

        $this->from(route('tasks.index'))
            ->post('/tasks', [
                'title' => 'Oversized task',
                'priority' => 'medium',
                'weight' => $weight,
            ])
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHasErrors('weight');

        $this->assertDatabaseCount('tasks', 0);
    }

    /**
     * @return array<string, array{0: int}>
     */
    public static function invalidWeights(): array
    {
        return [
            'zero' => [0],
            'nine' => [9],
        ];
    }

    public function test_create_assigns_workspace_member_and_persists_user_id(): void
    {
        $this->signIn();
        $project = Project::factory()->create();
        $assignee = User::factory()->create(['name' => 'Ada Lovelace']);
        $this->workspace?->users()->attach($assignee->id, ['role' => WorkspaceRole::Member->value]);

        $this->from(route('tasks.index'))
            ->post('/tasks', [
                'title' => 'Assigned in workspace',
                'project_id' => $project->id,
                'user_id' => $assignee->id,
                'priority' => 'medium',
                'weight' => 2,
            ])
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'title' => 'Assigned in workspace',
            'user_id' => $assignee->id,
            'weight' => 2,
        ]);
    }

    public function test_create_rejects_assignee_from_another_workspace(): void
    {
        $this->signIn();
        $project = Project::factory()->create();
        $otherWorkspace = Workspace::factory()->create();
        $outsider = User::factory()->create();
        $otherWorkspace->users()->attach($outsider->id, ['role' => WorkspaceRole::Member->value]);

        $this->from(route('tasks.index'))
            ->post('/tasks', [
                'title' => 'Cross workspace assign',
                'project_id' => $project->id,
                'user_id' => $outsider->id,
                'priority' => 'medium',
            ])
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_test_user_can_create_a_task_assigned_to_a_workspace_member(): void
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
        $this->signIn($user, $workspace);

        $assignee = User::factory()->create(['name' => 'Workspace Member']);
        $workspace->users()->attach($assignee->id, ['role' => WorkspaceRole::Member->value]);
        $project = Project::factory()->create();

        $this->from(route('tasks.index'))
            ->post('/tasks', [
                'title' => 'Seed-user assigned task',
                'project_id' => $project->id,
                'user_id' => $assignee->id,
                'priority' => 'high',
            ])
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'title' => 'Seed-user assigned task',
            'user_id' => $assignee->id,
        ]);
    }

    public function test_task_pages_pass_workspace_members_for_assignee_select(): void
    {
        $this->signIn();
        $this->withoutVite();

        $member = User::factory()->create(['name' => 'Member One']);
        $this->workspace?->users()->attach($member->id, ['role' => WorkspaceRole::Member->value]);

        $this->get('/tasks')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Tasks/Index')
                ->has('members', 2)
                ->has('users', 2)
                ->where('members', fn ($members) => collect($members)->contains('name', 'Member One'))
                ->where('users', fn ($members) => collect($members)->contains('name', 'Member One')));
    }
}
