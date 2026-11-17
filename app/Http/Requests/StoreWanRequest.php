<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StoreWanRequest extends BaseFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('wan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        $perimeterId = $this->input('perimeter_id') ?: auth()->user()?->activeOrDefaultPerimeterId();

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:32',
                Rule::unique('wans')->where('perimeter_id', $perimeterId)->whereNull('deleted_at'),
            ],
            'mans.*' => [
                'integer',
            ],
            'mans' => [
                'array',
            ],
            'lans.*' => [
                'integer',
            ],
            'lans' => [
                'array',
            ],
        ];
    }
}
