<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreOperationRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('operation_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreOperationRequest classique
        $storeRules = (new StoreOperationRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreOperationRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.activities'] = ['sometimes', 'array'];
        $rules['items.*.activities.*'] = ['integer'];

        $rules['items.*.actors'] = ['sometimes', 'array'];
        $rules['items.*.actors.*'] = ['integer'];

        $rules['items.*.tasks'] = ['sometimes', 'array'];
        $rules['items.*.tasks.*'] = ['integer'];

        return $rules;
    }
}
