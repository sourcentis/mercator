<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StoreDataProcessingRequest extends BaseFormRequest
{
    protected array $htmlFields = ['description', 'responsible', 'purpose', 'lawfulness', 'categories', 'recipients', 'transfert', 'retention', 'data_source', 'data_collection_obligation', 'automated_decision_making', 'data_subject_rights'];

    public function authorize(): bool
    {
        abort_if(Gate::denies('data_processing_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: auth()->user()?->activeOrDefaultPerimeterId();

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:64',
                'required',
                Rule::unique('data_processing')->where('perimeter_id', $perimeterId)->whereNull('deleted_at'),
            ],
            'operations.*' => [
                'integer',
            ],
            'operations' => [
                'array',
            ],
            'informations.*' => [
                'integer',
            ],
            'informations' => [
                'array',
            ],
            'processes.*' => [
                'integer',
            ],
            'processes' => [
                'array',
            ],
        ];
    }
}
