<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreDomainRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('domain_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreDomainRequest classique
        $storeRules = (new StoreDomainRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreDomainRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.forestAds'] = ['sometimes', 'array'];
        $rules['items.*.forestAds.*'] = ['integer'];

        return $rules;
    }
}
