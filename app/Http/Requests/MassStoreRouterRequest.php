<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreRouterRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('router_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreRouterRequest classique
        $storeRules = (new StoreRouterRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreRouterRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.physicalRouters'] = ['sometimes', 'array'];
        $rules['items.*.physicalRouters.*'] = ['integer'];

        return $rules;
    }
}
