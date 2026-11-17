<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateRelationRequest extends BaseFormRequest
{
    protected array $htmlFields = ['description', 'comments'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:32',
                'required',
                /* Not unique
                Rule::unique('relations')
                    ->ignore($this->route('relation')->id ?? $this->id)
                    ->whereNull('deleted_at'),
                */
            ],
            'importance' => [
                'nullable',
                'integer',
                'min:0',
                'max:4',
            ],
            'source_id' => [
                'required',
                'integer',
            ],
            'destination_id' => [
                'required',
                'integer',
            ],
            'documents' => [
                'array',
            ],
            'documents.*' => [
                'integer',
                'exists:documents,id',
            ],
            'start_validity' => [
                'date',
                'nullable',
            ],
            'end_validity' => [
                'date',
                'nullable',
                // TODO: fixme
                // 'after:start_validity',
            ],
        ];
    }
}
