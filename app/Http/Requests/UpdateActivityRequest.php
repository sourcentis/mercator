<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateActivityRequest extends BaseFormRequest
{
    protected array $htmlFields = ['description', 'drp'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('activity')?->perimeter_id;

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:64',
                'required',
                Rule::unique('activities')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('activity')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
            'operations.*' => ['integer'],
            'operations' => ['array'],

            'recovery_time_objective' => ['nullable', 'integer'],
            'maximum_tolerable_downtime' => ['nullable', 'integer'],
            'recovery_point_objective' => ['nullable', 'integer'],
            'maximum_tolerable_data_loss' => ['nullable', 'integer'],

        ];
    }
}
