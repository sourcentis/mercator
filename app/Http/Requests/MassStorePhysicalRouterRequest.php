<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStorePhysicalRouterRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('physical_router_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StorePhysicalRouterRequest classique
        $storeRules = (new StorePhysicalRouterRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StorePhysicalRouterRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.vlans'] = ['sometimes', 'array'];
        $rules['items.*.vlans.*'] = ['integer'];

        return $rules;
    }
}
