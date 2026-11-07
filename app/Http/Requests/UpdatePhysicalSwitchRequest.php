<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesIcon;
use Illuminate\Validation\Rule;

class UpdatePhysicalSwitchRequest extends BaseFormRequest
{
    use ValidatesIcon;

    protected array $htmlFields = ['description'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('physical_switch')?->perimeter_id;

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:2',
                'max:64',
                'required',
                Rule::unique('physical_switches')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('physical_switch')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}
