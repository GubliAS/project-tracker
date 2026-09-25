<?php

namespace App\Http\Requests\Initiation;

use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreStakeholderRequest extends FormRequest
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
            'project_id' => $this->projectIdRules(),
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'influence' => ['required', 'in:low,medium,high'],
            'interest' => ['required', 'in:low,medium,high'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return $this->afterProjectIsInWorkspace();
    }
}
