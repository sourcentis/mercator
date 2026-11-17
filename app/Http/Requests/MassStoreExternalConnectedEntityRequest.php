<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreExternalConnectedEntityRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('external_connected_entity_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreExternalConnectedEntityRequest classique
        $storeRules = (new StoreExternalConnectedEntityRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreExternalConnectedEntityRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.roles'] = ['sometimes', 'array'];
        $rules['items.*.roles.*'] = ['integer'];

        return $rules;
    }
}
