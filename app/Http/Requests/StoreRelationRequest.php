<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StoreRelationRequest extends BaseFormRequest
{
    protected array $htmlFields = ['description', 'comments'];

    public function authorize(): bool
    {
        abort_if(Gate::denies('relation_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:32',
                'required',
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
        ];
    }
}
