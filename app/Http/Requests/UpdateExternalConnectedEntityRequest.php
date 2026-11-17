<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateExternalConnectedEntityRequest extends BaseFormRequest
{
    protected array $htmlFields = ['description', 'security'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('external_connected_entity')?->perimeter_id;

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:32',
                'required',
                Rule::unique('external_connected_entities')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('external_connected_entity')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
            'src' => [
                'nullable',
                'ip',
            ],
        ];
    }
}
