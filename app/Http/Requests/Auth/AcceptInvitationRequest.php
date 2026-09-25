<?php

namespace App\Http\Requests\Auth;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AcceptInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        $invitation = Invitation::query()->where('token', $this->route('token'))->first();
        $user = $invitation
            ? User::query()->where('email', $invitation->email)->first()
            : null;

        if ($user && ! $user->must_set_password) {
            return [];
        }

        return [
            'password' => ['required', 'confirmed', Password::defaults()],
            'password_confirmation' => ['required', 'string'],
        ];
    }
}
