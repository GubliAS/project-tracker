<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use App\Models\Task;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTaskRequest extends FormRequest
{
    use AuthorizesWorkspaceWrite;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Task::class) ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'project_id' => $this->projectIdRules(),
            'user_id' => [
                'sometimes',
                'nullable',
                Rule::exists('workspace_user', 'user_id')->where(
                    'workspace_id',
                    $this->workspaceContext()->id() ?? 0,
                ),
            ],
            'status' => ['nullable', 'in:todo,in_progress,review,done'],
            'priority' => ['required', 'in:low,medium,high'],
            'weight' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:8'],
            'due_date' => ['sometimes', 'nullable', 'date'],
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
