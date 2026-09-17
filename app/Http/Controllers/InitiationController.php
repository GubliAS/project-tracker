<?php

namespace App\Http\Controllers;

use Inertia\Response;

class InitiationController extends Controller
{
    public function index(): Response
    {
        return $this->kickoff();
    }

    public function kickoff(): Response
    {
        return $this->inertiaPage('Initiation/Kickoff', 'Project Kick-Off');
    }

    public function stakeholders(): Response
    {
        return $this->inertiaPage('Initiation/Stakeholders', 'Stakeholders');
    }
}
