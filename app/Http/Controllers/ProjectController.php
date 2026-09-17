<?php

namespace App\Http\Controllers;

use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('Projects/Index', 'Projects List');
    }

    public function create(): Response
    {
        return $this->inertiaPage('Projects/Create', 'Create Project');
    }

    public function show(string $id): Response
    {
        return $this->inertiaPage('Projects/Show', 'Project Details', [
            'projectId' => $id,
        ]);
    }
}
