<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesLifecycleFields;
use App\Rules\IPList;
use Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StoreWifiTerminalRequest extends BaseFormRequest
{
    use ValidatesLifecycleFields;

    protected array $htmlFields = ['description'];

    public function authorize(): bool
    {
        abort_if(Gate::denies('wifi_terminal_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: auth()->user()?->activeOrDefaultPerimeterId();

        return [
            ...$this->lifecycleRules(['install_date', 'warranty_end_date', 'end_of_life_date', 'serial_number']),
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:32',
                'required',
                Rule::unique('wifi_terminals')->where('perimeter_id', $perimeterId)->whereNull('deleted_at'),
            ],
            'address_ip' => [
                'nullable',
                new IPList,
            ],
        ];
    }
}
