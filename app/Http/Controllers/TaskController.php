<?php

namespace App\Http\Controllers;

use Inertia\Response;

class TaskController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('Tasks/Index', 'Task List');
    }

    public function kanban(): Response
    {
        return $this->inertiaPage('Tasks/Kanban', 'Kanban Board');
    }

    public function workflows(): Response
    {
        return $this->inertiaPage('Tasks/Workflows', 'Workflows');
    }
}
