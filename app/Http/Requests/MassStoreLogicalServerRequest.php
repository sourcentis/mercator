<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreLogicalServerRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('logical_server_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreLogicalServerRequest classique
        $storeRules = (new StoreLogicalServerRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreLogicalServerRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.applications'] = ['sometimes', 'array'];
        $rules['items.*.applications.*'] = ['integer'];

        $rules['items.*.databases'] = ['sometimes', 'array'];
        $rules['items.*.databases.*'] = ['integer'];

        $rules['items.*.physicalServers'] = ['sometimes', 'array'];
        $rules['items.*.physicalServers.*'] = ['integer'];

        return $rules;
    }
}
