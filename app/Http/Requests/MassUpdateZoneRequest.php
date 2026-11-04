<?php

namespace App\Http\Requests;

use App\Models\Zone;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateZoneRequest extends BaseMassFormRequest
{
    public function authorize(): bool
    {
        abort_if(Gate::denies('zone_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        $updateRules = (new UpdateZoneRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:zones,id'],
        ];

        foreach ($updateRules as $field => $rule) {
            // Strip the unique rule: uniqueness is checked per-item in withValidator
            $rules["items.*.$field"] = $field === 'name'
                ? ['required', 'string', 'max:255']
                : $rule;
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('items', []) as $index => $item) {
                $id = $item['id'] ?? null;
                $name = $item['name'] ?? null;
                if ($name === null) {
                    continue;
                }
                $exists = Zone::where('name', $name)
                    ->whereNull('deleted_at')
                    ->where('id', '!=', $id)
                    ->exists();
                if ($exists) {
                    $validator->errors()->add("items.{$index}.name", __('validation.unique', ['attribute' => 'name']));
                }
            }
        });
    }
}
