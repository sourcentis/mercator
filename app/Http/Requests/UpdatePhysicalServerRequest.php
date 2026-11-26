<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesIcon;
use App\Http\Requests\Concerns\ValidatesLifecycleFields;
use App\Rules\IPList;
use Illuminate\Validation\Rule;

class UpdatePhysicalServerRequest extends BaseFormRequest
{
    use ValidatesIcon;
    use ValidatesLifecycleFields;

    protected array $htmlFields = ['description', 'configuration'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('physical_server')?->perimeter_id;

        return [
            ...$this->lifecycleRules(['install_date', 'update_date', 'warranty_end_date', 'end_of_life_date', 'manufacturer', 'model', 'serial_number']),
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:32',
                'required',
                Rule::unique('physical_servers')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('physical_server')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
            'address_ip' => [
                'nullable',
                new IPList,
            ],
        ];
    }
}
