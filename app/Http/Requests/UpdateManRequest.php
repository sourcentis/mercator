<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateManRequest extends BaseFormRequest
{
    protected array $htmlFields = ['description'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules()
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('man')?->perimeter_id;

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:32',
                'required',
                Rule::unique('mans')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('man')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
            'lans.*' => [
                'integer',
            ],
            'lans' => [
                'array',
            ],
        ];
    }
}
