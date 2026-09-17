<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

abstract class Controller
{
    protected function inertiaPage(string $page, string $title, array $props = []): Response
    {
        return Inertia::render($page, array_merge(['title' => $title], $props));
    }
}
