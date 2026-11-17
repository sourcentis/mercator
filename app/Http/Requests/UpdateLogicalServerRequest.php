<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesIcon;
use App\Rules\IPList;
use Illuminate\Validation\Rule;

class UpdateLogicalServerRequest extends BaseFormRequest
{
    use ValidatesIcon;

    protected array $htmlFields = ['description', 'configuration'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('logical_server')?->perimeter_id;

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:64',
                'required',
                Rule::unique('logical_servers')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('logical_server')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
            'disk' => [
                'nullable',
                'integer',
                'min:0',
                'max:2147483647',
            ],
            'address_ip' => [
                'nullable',
                new IPList,
            ],
        ];
    }
}
