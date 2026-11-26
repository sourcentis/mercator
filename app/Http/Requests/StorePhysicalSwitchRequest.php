<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesIcon;
use App\Http\Requests\Concerns\ValidatesLifecycleFields;
use Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StorePhysicalSwitchRequest extends BaseFormRequest
{
    use ValidatesIcon;
    use ValidatesLifecycleFields;

    protected array $htmlFields = ['description'];

    public function authorize(): bool
    {
        abort_if(Gate::denies('physical_switch_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: auth()->user()?->activeOrDefaultPerimeterId();

        return [
            ...$this->lifecycleRules(['install_date', 'warranty_end_date', 'end_of_life_date', 'manufacturer', 'model', 'serial_number']),
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:2',
                'max:64',
                'required',
                Rule::unique('physical_switches')->where('perimeter_id', $perimeterId)->whereNull('deleted_at'),
            ],
        ];
    }
}
