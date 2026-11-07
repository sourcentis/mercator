<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesIcon;
use Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StoreInformationRequest extends BaseFormRequest
{
    use ValidatesIcon;

    protected array $htmlFields = ['description', 'constraints'];

    public function authorize(): bool
    {
        abort_if(Gate::denies('information_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

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
                Rule::unique('information')->where('perimeter_id', $perimeterId)->whereNull('deleted_at'),
            ],
            'processes' => [
                'array',
            ],
            'processes.*' => [
                'integer',
            ],
            'parents' => [
                'array',
            ],
            'parents.*' => [
                'integer',
            ],
            'children' => [
                'array',
            ],
            'children.*' => [
                'integer',
            ],
            'security_need' => [
                'nullable',
                'integer',
                'min:0',
                'max:5',
            ],
        ];
    }
}
