<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StoreExternalConnectedEntityRequest extends BaseFormRequest
{
    protected array $htmlFields = ['description', 'security'];

    public function authorize(): bool
    {
        abort_if(Gate::denies('external_connected_entity_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: auth()->user()?->activeOrDefaultPerimeterId();

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:32',
                'required',
                Rule::unique('external_connected_entities')->where('perimeter_id', $perimeterId)->whereNull('deleted_at'),
            ],
            'src' => [
                'nullable',
                'ip',
            ],
        ];
    }
}
