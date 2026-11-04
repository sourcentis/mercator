<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreProcessRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('process_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreProcessRequest classique
        $storeRules = (new StoreProcessRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreProcessRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.activities'] = ['sometimes', 'array'];
        $rules['items.*.activities.*'] = ['integer'];

        $rules['items.*.applications'] = ['sometimes', 'array'];
        $rules['items.*.applications.*'] = ['integer'];

        $rules['items.*.entities'] = ['sometimes', 'array'];
        $rules['items.*.entities.*'] = ['integer'];

        $rules['items.*.informations'] = ['sometimes', 'array'];
        $rules['items.*.informations.*'] = ['integer'];

        $rules['items.*.operations'] = ['sometimes', 'array'];
        $rules['items.*.operations.*'] = ['integer'];

        return $rules;
    }
}
