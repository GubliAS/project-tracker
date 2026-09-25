<?php

namespace App\Http\Requests\Quality;

use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreRiskRequest extends FormRequest
{
    use AuthorizesWorkspaceWrite;

    public function authorize(): bool
    {
        return $this->workspaceContext()->canWriteMemberContent();
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'impact' => ['required', 'in:low,medium,high'],
            'probability' => ['required', 'in:low,medium,high'],
            'status' => ['required', 'in:open,monitoring,mitigated,closed'],
            'mitigation_plan' => ['nullable', 'string'],
            'project_id' => $this->projectIdRules(),
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
