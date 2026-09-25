<?php

namespace App\Http\Requests\Project;

use App\Enums\Currency;
use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use App\Models\Document;
use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    use AuthorizesWorkspaceWrite;

    public function authorize(): bool
    {
        return $this->hasCurrentWorkspace()
            && ($this->user()?->can('create', Project::class) ?? false);
    }

    protected function prepareForValidation(): void
    {
        $documents = $this->file('documents');

        if ($documents instanceof UploadedFile) {
            $this->files->set('documents', [$documents]);
        }
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:planning,active,on_hold,completed'],
            'team' => ['nullable', 'string', 'max:255'],
            'client' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'project_type' => ['nullable', 'in:hybrid,predictive,agile'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'spent' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', Rule::enum(Currency::class)],
            'settings' => ['nullable', 'array'],
            'documents' => ['nullable', 'array'],
            'documents.*' => Document::uploadRules(required: false),
            'document_category' => Document::categoryRules(required: false),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return Document::uploadMessagesFor('documents.*');
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [
            'documents.*' => 'This file',
        ];

        foreach ($this->uploadedDocumentFiles() as $index => $file) {
            $name = $file->getClientOriginalName();

            if ($name !== '') {
                $attributes['documents.'.$index] = $name;
            }
        }

        return $attributes;
    }

    /**
     * @return array<int|string, UploadedFile>
     */
    private function uploadedDocumentFiles(): array
    {
        $documents = $this->file('documents');

        if ($documents instanceof UploadedFile) {
            return [0 => $documents];
        }

        if (! is_array($documents)) {
            return [];
        }

        return array_filter(
            $documents,
            fn (mixed $file): bool => $file instanceof UploadedFile,
        );
    }
}
