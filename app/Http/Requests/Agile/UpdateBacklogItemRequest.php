<?php

namespace App\Http\Requests\Agile;

use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateBacklogItemRequest extends FormRequest
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
            'project_id' => $this->projectIdRules(partial: true),
            'sprint_id' => ['nullable', 'integer', 'exists:sprints,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'in:epic,feature,story'],
            'priority' => ['sometimes', 'required', 'in:low,medium,high'],
            'points' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', 'in:backlog,ready,in-progress,done'],
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
