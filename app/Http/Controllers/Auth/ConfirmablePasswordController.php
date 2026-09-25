<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConfirmPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

class ConfirmablePasswordController extends Controller
{
    public function show(): Response
    {
        return $this->inertiaPage('Auth/ConfirmPassword', 'Confirm password');
    }

    public function store(ConfirmPasswordRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (! Auth::guard('web')->validate([
            'email' => $request->user()?->email,
            'password' => $validated['password'],
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
