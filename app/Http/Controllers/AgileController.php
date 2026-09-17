<?php

namespace App\Http\Controllers;

use Inertia\Response;

class AgileController extends Controller
{
    public function sprints(): Response
    {
        return $this->inertiaPage('Agile/Sprints', 'Sprints');
    }

    public function backlog(): Response
    {
        return $this->inertiaPage('Agile/Backlog', 'Backlog');
    }

    public function definitions(): Response
    {
        return $this->inertiaPage('Agile/Definitions', 'DoR / DoD');
    }
}
