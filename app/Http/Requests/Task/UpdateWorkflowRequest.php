<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'stages' => ['sometimes', 'array'],
            'stages.*' => ['string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('stages')) {
            return;
        }

        $stages = $this->input('stages');

        if (is_string($stages)) {
            $stages = preg_split('/\s*,\s*/', $stages, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $this->merge([
            'stages' => array_values(array_filter(array_map(
                fn (mixed $stage): string => trim((string) $stage),
                is_array($stages) ? $stages : [],
            ))),
        ]);
    }
}
