<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteViewResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    public function test_primary_navigation_routes_render_their_current_inertia_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Dashboard')->has('kpis'));

        $this->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Dashboard')->has('kpis')->has('runningProjects'));

        $this->get('/projects')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Projects/Index')->has('projects'));

        $this->get('/initiation')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Initiation'));

        $this->get('/agile')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Agile'));

        $this->get('/tasks')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Tasks/Index'));

        $this->get('/tasks/kanban')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Tasks/Kanban'));

        $this->get('/resources')
            ->assertRedirect(route('resources.team'));

        $this->get('/resources/team')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Resources/Team'));

        $this->get('/quality')
            ->assertRedirect(route('quality.qa-testing'));

        $this->get('/reports')
            ->assertRedirect(route('reports.analytics'));

        $this->get('/chat')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Chat/Index'));
    }

    public function test_quality_and_reports_navigation_routes_render_their_registered_pages(): void
    {
        $this->get('/quality/change-log')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('DatabaseList')->has('items'));

        $this->get('/quality/qa-testing')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('DatabaseList')->has('items'));

        $this->get('/quality/risks')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('DatabaseList')->has('items'));

        $this->get('/reports/analytics')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Reports/Index')->has('stats'));

        $this->get('/reports/documents')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('DatabaseList')->has('items'));

        $this->get('/reports/lessons')
            ->assertRedirect('/reports/lessons-learned');

        $this->get('/reports/lessons-learned')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Reports/Lessons')->has('lessons'));
    }
}
