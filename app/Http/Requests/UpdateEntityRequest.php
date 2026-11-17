<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesIcon;
use Illuminate\Validation\Rule;

class UpdateEntityRequest extends BaseFormRequest
{
    use ValidatesIcon;

    protected array $htmlFields = ['description', 'security_level', 'contact_point'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('entity')?->perimeter_id;

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:64',
                'required',
                Rule::unique('entities')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('entity')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
            'seurity_level' => [
                'nullable',
                'integer',
                'min:0',
                'max:5',
            ],
        ];
    }
}
