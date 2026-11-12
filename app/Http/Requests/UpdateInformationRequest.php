<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesIcon;
use Illuminate\Validation\Rule;

class UpdateInformationRequest extends BaseFormRequest
{
    use ValidatesIcon;

    protected array $htmlFields = ['description', 'constraints', 'retention_notes'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('information')?->perimeter_id;

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:64',
                'required',
                Rule::unique('information')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('information')->id ?? $this->id)
                    ->whereNull('deleted_at'),
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
            'retention_period' => [
                'nullable',
                'string',
                'max:255',
            ],
            'final_disposition' => [
                'nullable',
                'string',
                'max:255',
            ],
            'retention_notes' => [
                'nullable',
                'string',
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
