<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesIcon;
use App\Rules\IPList;
use Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StoreLogicalServerRequest extends BaseFormRequest
{
    use ValidatesIcon;

    protected array $htmlFields = ['description', 'configuration'];

    public function authorize(): bool
    {
        abort_if(Gate::denies('logical_server_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: auth()->user()?->activeOrDefaultPerimeterId();

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:64',
                'required',
                Rule::unique('logical_servers')->where('perimeter_id', $perimeterId)->whereNull('deleted_at'),
            ],
            'servers.*' => [
                'integer',
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
            'servers' => [
                'array',
            ],
        ];
    }
}
