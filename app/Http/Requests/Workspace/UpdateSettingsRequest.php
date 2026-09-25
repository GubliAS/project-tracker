<?php

namespace App\Http\Requests\Workspace;

use App\Enums\Currency;
use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use App\Models\Workspace;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    use AuthorizesWorkspaceWrite;

    public function authorize(): bool
    {
        $workspace = $this->workspaceContext()->workspace();

        return $workspace instanceof Workspace
            && ($this->user()?->can('update', $workspace) ?? false);
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'currency' => ['required', 'string', Rule::enum(Currency::class)],
        ];
    }
}
