<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateZoneRequest extends BaseFormRequest
{
    protected array $htmlFields = ['description'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('zone')?->perimeter_id;

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => ['required', 'string', 'max:255', Rule::unique('zones', 'name')->where('perimeter_id', $perimeterId)->ignore($this->route('zone')->id ?? $this->id)->whereNull('deleted_at')],
            'type' => 'nullable|string|max:255',
            'attributes' => 'nullable',
            'description' => 'nullable|string',
            'parentZones' => 'nullable|array',
            'parentZones.*' => 'exists:zones,id',
            'childZones' => 'nullable|array',
            'childZones.*' => 'exists:zones,id',
            'buildings' => 'nullable|array',
            'buildings.*' => 'exists:buildings,id',
            'adminUsers' => 'nullable|array',
            'adminUsers.*' => 'exists:admin_users,id',
        ];
    }
}
