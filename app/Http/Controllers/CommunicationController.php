<?php

namespace App\Http\Controllers;

use Inertia\Response;

class CommunicationController extends Controller
{
    public function chat(): Response
    {
        return $this->inertiaPage('Communication/Chat', 'Project Chat');
    }
}
