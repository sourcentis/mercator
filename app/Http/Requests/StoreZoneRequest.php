<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StoreZoneRequest extends BaseFormRequest
{
    protected array $htmlFields = ['description'];

    public function authorize(): bool
    {
        abort_if(Gate::denies('zone_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: auth()->user()?->activeOrDefaultPerimeterId();

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => ['required', 'string', 'max:255', Rule::unique('zones', 'name')->where('perimeter_id', $perimeterId)->whereNull('deleted_at')],
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
