<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesIcon;
use App\Http\Requests\Concerns\ValidatesLifecycleFields;
use App\Rules\IPList;
use Illuminate\Validation\Rule;

class UpdateWorkstationRequest extends BaseFormRequest
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
        $perimeterId = $this->input('perimeter_id') ?: $this->route('workstation')?->perimeter_id;

        return [
            ...$this->lifecycleRules(['install_date', 'end_of_life_date', 'purchase_date', 'fin_value', 'warranty', 'warranty_start_date', 'warranty_end_date', 'warranty_period']),
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:32',
                'required',
                Rule::unique('workstations')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('workstation')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
            'address_ip' => [
                'nullable',
                new IPList,
            ],
            'applications' => ['array', 'nullable'],
            'applications.*' => ['exists:applications,id'],
        ];
    }
}
