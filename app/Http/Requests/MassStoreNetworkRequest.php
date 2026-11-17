<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreNetworkRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('network_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreNetworkRequest classique
        $storeRules = (new StoreNetworkRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreNetworkRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.roles'] = ['sometimes', 'array'];
        $rules['items.*.roles.*'] = ['integer'];

        return $rules;
    }
}
