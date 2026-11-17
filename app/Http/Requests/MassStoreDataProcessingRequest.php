<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreDataProcessingRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('data_processing_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreDataProcessingRequest classique
        $storeRules = (new StoreDataProcessingRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreDataProcessingRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.applications'] = ['sometimes', 'array'];
        $rules['items.*.applications.*'] = ['integer'];

        $rules['items.*.informations'] = ['sometimes', 'array'];
        $rules['items.*.informations.*'] = ['integer'];

        $rules['items.*.processes'] = ['sometimes', 'array'];
        $rules['items.*.processes.*'] = ['integer'];

        return $rules;
    }
}
