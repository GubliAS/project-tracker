<?php

namespace App\Http\Requests\Chat;

use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreChatMessageRequest extends FormRequest
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
            'project_id' => $this->projectIdRules(required: true),
            'message' => ['required', 'string', 'max:5000'],
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
