<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use App\Models\Task;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTaskRequest extends FormRequest
{
    use AuthorizesWorkspaceWrite;

    public function authorize(): bool
    {
        $task = $this->route('task');

        return $task instanceof Task
            && (
                ($this->user()?->can('update', $task) ?? false)
                || ($this->user()?->can('updateStatus', $task) ?? false)
            );
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        $task = $this->route('task');

        if ($task instanceof Task && ($this->user()?->can('update', $task) ?? false)) {
            return [
                'title' => ['sometimes', 'required', 'string', 'max:255'],
                'description' => ['sometimes', 'nullable', 'string'],
                'project_id' => $this->projectIdRules(partial: true),
                'user_id' => [
                    'sometimes',
                    'nullable',
                    Rule::exists('workspace_user', 'user_id')->where(
                        'workspace_id',
                        $this->workspaceContext()->id() ?? 0,
                    ),
                ],
                'status' => ['sometimes', 'in:todo,in_progress,review,done'],
                'priority' => ['sometimes', 'in:low,medium,high'],
                'weight' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:8'],
                'due_date' => ['sometimes', 'nullable', 'date'],
            ];
        }

        return [
            'status' => ['required', 'in:todo,in_progress,review,done'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $task = $this->route('task');

                if (! $task instanceof Task || ($this->user()?->can('update', $task) ?? false)) {
                    return;
                }

                foreach (['title', 'description', 'priority', 'weight', 'due_date', 'user_id', 'project_id'] as $field) {
                    abort_if($this->exists($field), 403);
                }
            },
            ...$this->afterProjectIsInWorkspace(),
        ];
    }
}
