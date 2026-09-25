<?php

namespace App\Http\Requests\Initiation;

use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateKickoffRequest extends FormRequest
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
            'project_id' => $this->projectIdRules(partial: true),
            'scheduled_on' => ['sometimes', 'required', 'date'],
            'attendees' => ['sometimes', 'required', 'integer', 'min:0', 'max:500'],
            'status' => ['sometimes', 'required', 'in:scheduled,completed'],
            'objectives' => ['nullable', 'array'],
            'objectives.*.text' => ['required_with:objectives', 'string', 'max:255'],
            'objectives.*.completed' => ['nullable', 'boolean'],
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
