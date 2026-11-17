<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesIcon;
use Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StoreProcessRequest extends BaseFormRequest
{
    use ValidatesIcon;

    protected array $htmlFields = ['description', 'in_out'];

    public function authorize(): bool
    {
        abort_if(Gate::denies('process_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

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
                Rule::unique('processes')->where('perimeter_id', $perimeterId)->whereNull('deleted_at'),
            ],
            'activities.*' => [
                'integer',
            ],
            'activities' => [
                'array',
            ],
            'entities.*' => [
                'integer',
            ],
            'entities' => [
                'array',
            ],
            'security_need' => [
                'nullable',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
        ];
    }
}
