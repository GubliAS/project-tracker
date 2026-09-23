<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
