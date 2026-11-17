<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StoreSecurityControlRequest extends BaseFormRequest
{
    protected array $htmlFields = ['description'];

    public function authorize()
    {
        abort_if(Gate::denies('security_control_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        $perimeterId = $this->input('perimeter_id') ?: auth()->user()?->activeOrDefaultPerimeterId();

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:255',
                'required',
                Rule::unique('security_controls')->where('perimeter_id', $perimeterId)->whereNull('deleted_at'),
            ],
        ];
    }
}
