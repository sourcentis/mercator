<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreInformationRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('information_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreInformationRequest classique
        $storeRules = (new StoreInformationRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreInformationRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.processes'] = ['sometimes', 'array'];
        $rules['items.*.processes.*'] = ['integer'];

        return $rules;
    }
}
