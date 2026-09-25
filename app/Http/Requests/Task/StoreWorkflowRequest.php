<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\Concerns\AuthorizesWorkspaceWrite;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkflowRequest extends FormRequest
{
    use AuthorizesWorkspaceWrite;

    public function authorize(): bool
    {
        return $this->hasCurrentWorkspace() && $this->workspaceContext()->canWriteOps();
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'stages' => ['required', 'array'],
            'stages.*' => ['string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->mergeNormalizedStages();
    }

    protected function mergeNormalizedStages(): void
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
