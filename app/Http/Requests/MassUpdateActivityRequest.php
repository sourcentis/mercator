<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateActivityRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('activity_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du update classique
        $updateRules = (new UpdateActivityRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:activities,id'],
        ];

        // On applique les règles de UpdateActivityRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        // Relations
        $rules['items.*.operations'] = ['sometimes', 'array'];
        $rules['items.*.operations.*'] = ['integer'];
        $rules['items.*.processes'] = ['sometimes', 'array'];
        $rules['items.*.processes.*'] = ['integer'];

        return $rules;
    }
}
