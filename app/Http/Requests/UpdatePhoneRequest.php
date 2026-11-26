<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesLifecycleFields;
use App\Rules\IPList;
use Illuminate\Validation\Rule;

class UpdatePhoneRequest extends BaseFormRequest
{
    use ValidatesLifecycleFields;

    protected array $htmlFields = ['description'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('phone')?->perimeter_id;

        return [
            ...$this->lifecycleRules(['install_date', 'warranty_end_date', 'end_of_life_date', 'manufacturer', 'model', 'serial_number']),
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:32',
                'required',
                Rule::unique('phones')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('phone')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
            'address_ip' => [
                'nullable',
                new IPList,
            ],
        ];
    }
}
