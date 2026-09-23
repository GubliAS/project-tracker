<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AuthorizesWorkspace;
use App\Support\WorkspaceContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
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
     * @return Collection<int, array{id: int, name: string}>
     */
    protected function workspaceMembers(): Collection
    {
        $workspace = $this->workspace()->workspace();

        if ($workspace === null) {
            return collect();
        }

        return $workspace->users()
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
            ])
            ->values();
    }

    /**
     * @param  array<string, mixed>  $props
     */
    protected function inertiaPage(string $page, string $title, array $props = []): Response
    {
        return Inertia::render($page, array_merge(['title' => $title], $props));
    }
}
