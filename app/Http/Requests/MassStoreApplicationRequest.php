<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreApplicationRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('application_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreApplicationRequest classique
        $storeRules = (new StoreApplicationRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreApplicationRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        // Relations many-to-many / attach par IDs
        $rules['items.*.entities'] = ['sometimes', 'array'];
        $rules['items.*.entities.*'] = ['integer'];

        $rules['items.*.processes'] = ['sometimes', 'array'];
        $rules['items.*.processes.*'] = ['integer'];

        $rules['items.*.activities'] = ['sometimes', 'array'];
        $rules['items.*.activities.*'] = ['integer'];

        $rules['items.*.databases'] = ['sometimes', 'array'];
        $rules['items.*.databases.*'] = ['integer'];

        $rules['items.*.logical_servers'] = ['sometimes', 'array'];
        $rules['items.*.logical_servers.*'] = ['integer'];

        $rules['items.*.application_services'] = ['sometimes', 'array'];
        $rules['items.*.application_services.*'] = ['integer'];

        return $rules;
    }
}
