<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreCertificateRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('certificate_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreCertificateRequest classique
        $storeRules = (new StoreCertificateRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreCertificateRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.applications'] = ['sometimes', 'array'];
        $rules['items.*.applications.*'] = ['integer'];

        $rules['items.*.logical_servers'] = ['sometimes', 'array'];
        $rules['items.*.logical_servers.*'] = ['integer'];

        return $rules;
    }
}
