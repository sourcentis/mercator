<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateDataProcessingRequest extends BaseFormRequest
{
    protected array $htmlFields = ['description', 'responsible', 'purpose', 'lawfulness', 'categories', 'recipients', 'transfert', 'retention', 'data_source', 'data_collection_obligation', 'automated_decision_making', 'data_subject_rights'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('data_processing')?->perimeter_id;

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:64',
                'required',
                Rule::unique('data_processing')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('data_processing')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
            'processes.*' => [
                'integer',
            ],
            'processes' => [
                'array',
            ],
            'applications.*' => [
                'integer',
            ],
            'applications' => [
                'array',
            ],
            'informations.*' => [
                'integer',
            ],
            'informations' => [
                'array',
            ],
        ];
    }
}
