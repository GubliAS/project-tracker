<?php

namespace App\Http\Controllers;

use Inertia\Response;

class QualityController extends Controller
{
    public function qaTesting(): Response
    {
        return $this->inertiaPage('Quality/QaTesting', 'QA & Testing');
    }

    public function risks(): Response
    {
        return $this->inertiaPage('Quality/Risks', 'Risks & Issues');
    }

    public function changeLog(): Response
    {
        return $this->inertiaPage('Quality/ChangeLog', 'Change Log');
    }
}
