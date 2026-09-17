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
}
