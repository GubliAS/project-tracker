<?php

namespace App\Http\Requests\Workspace;

use App\Enums\Currency;
use App\Models\Workspace;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Workspace::class) ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        $membershipIds = $this->user()?->workspaces()->pluck('workspaces.id') ?? collect();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('workspaces', 'name')
                    ->whereNull('deleted_at')
                    ->where(fn ($query) => $query->whereIn('id', $membershipIds)),
            ],
            'currency' => ['nullable', 'string', Rule::enum(Currency::class)],
        ];
    }
}
