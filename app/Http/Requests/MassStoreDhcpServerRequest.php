<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreDhcpServerRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('dhcp_server_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreDhcpServerRequest classique
        $storeRules = (new StoreDhcpServerRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreDhcpServerRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.roles'] = ['sometimes', 'array'];
        $rules['items.*.roles.*'] = ['integer'];

        return $rules;
    }
}
