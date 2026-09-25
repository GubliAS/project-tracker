<?php

namespace App\Http\Requests\Project;

use App\Enums\Currency;
use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use App\Models\Document;
use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    use AuthorizesWorkspaceWrite;

    public function authorize(): bool
    {
        return $this->hasCurrentWorkspace()
            && ($this->user()?->can('create', Project::class) ?? false);
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
}
