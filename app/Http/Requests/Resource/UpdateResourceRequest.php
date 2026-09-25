<?php

namespace App\Http\Requests\Resource;

use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateResourceRequest extends FormRequest
{
    use AuthorizesWorkspaceWrite;

    public function authorize(): bool
    {
        return $this->workspaceContext()->canWriteOps();
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'type' => ['sometimes', 'required', 'in:human,hardware,software,material'],
            'role_or_category' => ['nullable', 'string', 'max:255'],
            'cost_per_hour' => ['sometimes', 'required', 'numeric', 'min:0'],
            'availability_status' => ['sometimes', 'required', 'in:available,allocated,unavailable'],
            'availability_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
