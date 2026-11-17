<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateSecurityControlRequest extends BaseFormRequest
{
    protected array $htmlFields = ['description'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules()
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('security_control')?->perimeter_id;

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:255',
                'required',
                Rule::unique('security_controls')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('security_control')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}
