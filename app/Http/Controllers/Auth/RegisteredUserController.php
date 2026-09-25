<?php

namespace App\Http\Controllers\Auth;

use App\Enums\WorkspaceRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return $this->inertiaPage('Auth/Register', 'Create account');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = User::query()->create($request->safe()->only(['name', 'email', 'password']));

            $workspace = Workspace::query()->create([
                'name' => $user->name."'s workspace",
            ]);

            $workspace->users()->attach($user->id, [
                'role' => WorkspaceRole::WorkspaceAdmin->value,
            ]);

            $request->session()->put('current_workspace_id', $workspace->id);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }
}
