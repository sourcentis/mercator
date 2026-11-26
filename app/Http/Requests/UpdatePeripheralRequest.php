<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesIcon;
use App\Http\Requests\Concerns\ValidatesLifecycleFields;
use App\Rules\IPList;
use Illuminate\Validation\Rule;

class UpdatePeripheralRequest extends BaseFormRequest
{
    use ValidatesIcon;
    use ValidatesLifecycleFields;

    protected array $htmlFields = ['description'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('peripheral')?->perimeter_id;

        return [
            ...$this->lifecycleRules(['install_date', 'warranty_end_date', 'end_of_life_date', 'serial_number']),
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:32',
                'required',
                Rule::unique('peripherals')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('peripheral')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
            'address_ip' => [
                'nullable',
                new IPList,
            ],
        ];
    }
}
