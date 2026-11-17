<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStorePermissionRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('permission_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StorePermissionRequest classique
        $storeRules = (new StorePermissionRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StorePermissionRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
