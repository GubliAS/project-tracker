<?php

namespace Tests;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected ?Workspace $workspace = null;

    protected function signIn(?User $user = null, ?Workspace $workspace = null, WorkspaceRole $role = WorkspaceRole::WorkspaceAdmin): User
    {
        $user ??= User::factory()->create();
        $workspace ??= Workspace::factory()->create();

        if (! $user->belongsToWorkspace($workspace)) {
            $workspace->users()->attach($user->id, ['role' => $role->value]);
        }

        $this->workspace = $workspace;
        $this->actingAs($user);
        session(['current_workspace_id' => $workspace->id]);
        $this->withSession(['current_workspace_id' => $workspace->id]);

        return $user;
    }

    protected function signInAs(WorkspaceRole $role, ?Workspace $workspace = null): User
    {
        return $this->signIn(null, $workspace, $role);
    }
}
