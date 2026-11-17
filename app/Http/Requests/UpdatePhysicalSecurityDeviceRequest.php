<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesIcon;
use App\Rules\IPList;
use Illuminate\Validation\Rule;

class UpdatePhysicalSecurityDeviceRequest extends BaseFormRequest
{
    use ValidatesIcon;

    protected array $htmlFields = ['description'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('physical_security_device')?->perimeter_id;

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:32',
                'required',
                Rule::unique('physical_security_devices')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('physical_security_device')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
            'address_ip' => [
                'nullable',
                new IPList,
            ],
        ];
    }
}
