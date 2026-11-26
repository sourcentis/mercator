<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesLifecycleFields;
use Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StorePhysicalRouterRequest extends BaseFormRequest
{
    use ValidatesLifecycleFields;

    protected array $htmlFields = ['description'];

    public function authorize(): bool
    {
        abort_if(Gate::denies('physical_router_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: auth()->user()?->activeOrDefaultPerimeterId();

        return [
            ...$this->lifecycleRules(['install_date', 'warranty_end_date', 'end_of_life_date', 'manufacturer', 'model', 'serial_number']),
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:32',
                'required',
                Rule::unique('physical_routers')->where('perimeter_id', $perimeterId)->whereNull('deleted_at'),
            ],
            'vlans.*' => [
                'integer',
            ],
            'vlans' => [
                'array',
            ],
        ];
    }
}
