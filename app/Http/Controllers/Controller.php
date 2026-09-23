<?php

namespace App\Http\Controllers;

use App\Support\AuthorizesWorkspace;
use App\Support\WorkspaceContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

abstract class Controller
{
    use AuthorizesRequests;

    protected function workspace(): WorkspaceContext
    {
        return app(WorkspaceContext::class);
    }

    protected function authorizer(): AuthorizesWorkspace
    {
        return app(AuthorizesWorkspace::class);
    }

    protected function currentWorkspaceId(): ?int
    {
        return $this->workspace()->id();
    }

    /**
     * @param  array<string, mixed>  $props
     */
    protected function inertiaPage(string $page, string $title, array $props = []): Response
    {
        return Inertia::render($page, array_merge(['title' => $title], $props));
    }
}
