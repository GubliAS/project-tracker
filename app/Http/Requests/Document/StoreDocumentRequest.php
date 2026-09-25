<?php

namespace App\Http\Requests\Document;

use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use App\Models\Document;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
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
     * @return array<string, string>
     */
    public function messages(): array
    {
        return Document::uploadMessagesFor('file');
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $file = $this->file('file');

        return [
            'file' => $file instanceof UploadedFile && $file->getClientOriginalName() !== ''
                ? $file->getClientOriginalName()
                : 'This file',
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
