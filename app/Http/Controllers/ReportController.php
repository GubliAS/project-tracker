<?php

namespace App\Http\Controllers;

use Inertia\Response;

class ReportController extends Controller
{
    public function analytics(): Response
    {
        return $this->inertiaPage('Reports/Analytics', 'Reports & Analytics');
    }

    public function documents(): Response
    {
        return $this->inertiaPage('Reports/Documents', 'Documents');
    }

    public function lessonsLearned(): Response
    {
        return $this->inertiaPage('Reports/LessonsLearned', 'Lessons Learned');
    }
}
