<?php

namespace App\Http\Requests\Document;

use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use App\Models\Document;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDocumentRequest extends FormRequest
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
            'file' => Document::uploadRules(),
            'category' => Document::categoryRules(),
            'project_id' => $this->projectIdRules(),
            'return_to_project' => ['sometimes', 'boolean'],
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
