<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteViewResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_navigation_routes_render_their_current_inertia_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Dashboard'));

        $this->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Dashboard'));

        $this->get('/projects')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Projects'));

        $this->get('/initiation')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Initiation'));

        $this->get('/agile')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Agile'));

        $this->get('/tasks')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Tasks/Index'));

        $this->get('/resources')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Resources/Index'));

        $this->get('/quality')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Quality/Index'));

        $this->get('/reports')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Reports/Index'));
    }

    public function test_quality_and_reports_navigation_routes_render_their_registered_pages(): void
    {
        $this->get('/quality/change-log')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Quality/ChangeLog'));

        $this->get('/quality/qa-testing')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Quality/Testing'));

        $this->get('/quality/risks')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Quality/Risks'));

        $this->get('/reports/analytics')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Reports/Index'));

        $this->get('/reports/documents')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Reports/Documents'));

        $this->get('/reports/lessons')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Reports/LessonsLearned'));
    }
}
