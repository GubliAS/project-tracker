<?php

namespace App\Http\Requests;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Support\WorkspaceContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class InviteWorkspaceMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workspace = app(WorkspaceContext::class)->workspace();

        return $workspace !== null && ($this->user()?->can('manageMembers', $workspace) ?? false);
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::enum(WorkspaceRole::class)],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $email = Str::lower((string) $this->input('email'));
                $workspace = app(WorkspaceContext::class)->workspace();

                if ($workspace && $workspace->users()->where('users.email', $email)->exists()) {
                    $validator->errors()->add('email', 'That user is already a member of this workspace.');

                    return;
                }

                $existing = User::query()->where('email', $email)->first();

                if ($existing) {
                    return;
                }

                $name = filled($this->input('name'))
                    ? (string) $this->input('name')
                    : Str::before($email, '@');

                if (User::query()->where('name', $name)->exists()) {
                    $validator->errors()->add('name', 'A user with this name already exists.');
                }
            },
        ];
    }
}
