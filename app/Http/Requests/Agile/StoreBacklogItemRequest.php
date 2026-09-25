<?php

namespace App\Http\Requests\Agile;

use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBacklogItemRequest extends FormRequest
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
            'sprint_id' => ['nullable', 'integer', 'exists:sprints,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:epic,feature,story'],
            'priority' => ['required', 'in:low,medium,high'],
            'points' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:backlog,ready,in-progress,done'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return $this->afterRelatedIdsAreInWorkspace();
    }
}
