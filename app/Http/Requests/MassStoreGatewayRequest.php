<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreGatewayRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('gateway_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreGatewayRequest classique
        $storeRules = (new StoreGatewayRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreGatewayRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.subnetworks'] = ['sometimes', 'array'];
        $rules['items.*.subnetworks.*'] = ['integer'];

        return $rules;
    }
}
