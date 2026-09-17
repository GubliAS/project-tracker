<?php

namespace App\Http\Controllers;

use Inertia\Response;

class ResourceController extends Controller
{
    public function team(): Response
    {
        return $this->inertiaPage('Resources/Team', 'Team Resources');
    }

    public function timeTracking(): Response
    {
        return $this->inertiaPage('Resources/TimeTracking', 'Time Tracking');
    }

    public function budget(): Response
    {
        return $this->inertiaPage('Resources/Budget', 'Budget');
    }

    public function milestones(): Response
    {
        return $this->inertiaPage('Resources/Milestones', 'Milestones');
    }

    public function gantt(): Response
    {
        return $this->inertiaPage('Resources/Gantt', 'Gantt Chart');
    }
}
