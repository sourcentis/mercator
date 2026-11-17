<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreNetworkSwitchRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('network_switch_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreNetworkSwitchRequest classique
        $storeRules = (new StoreNetworkSwitchRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreNetworkSwitchRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.physicalSwitches'] = ['sometimes', 'array'];
        $rules['items.*.physicalSwitches.*'] = ['integer'];

        $rules['items.*.vlans'] = ['sometimes', 'array'];
        $rules['items.*.vlans.*'] = ['integer'];

        return $rules;
    }
}
