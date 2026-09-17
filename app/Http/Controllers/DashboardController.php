<?php

namespace App\Http\Controllers;

use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return $this->inertiaPage('Dashboard', 'Dashboard');
    }
}
