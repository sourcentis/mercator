<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreLanRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('lan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreLanRequest classique
        $storeRules = (new StoreLanRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreLanRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.mans'] = ['sometimes', 'array'];
        $rules['items.*.mans.*'] = ['integer'];

        $rules['items.*.wans'] = ['sometimes', 'array'];
        $rules['items.*.wans.*'] = ['integer'];

        return $rules;
    }
}
