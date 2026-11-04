<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreDatabaseRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('database_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreDatabaseRequest classique
        $storeRules = (new StoreDatabaseRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreDatabaseRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.applications'] = ['sometimes', 'array'];
        $rules['items.*.applications.*'] = ['integer'];

        $rules['items.*.entities'] = ['sometimes', 'array'];
        $rules['items.*.entities.*'] = ['integer'];

        $rules['items.*.informations'] = ['sometimes', 'array'];
        $rules['items.*.informations.*'] = ['integer'];

        $rules['items.*.logical_servers'] = ['sometimes', 'array'];
        $rules['items.*.logical_servers.*'] = ['integer'];

        $rules['items.*.roles'] = ['sometimes', 'array'];
        $rules['items.*.roles.*'] = ['integer'];

        return $rules;
    }
}
