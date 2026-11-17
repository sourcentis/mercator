<?php

namespace App\Http\Requests;

use App\Models\Dnsserver;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateDnsserverRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('dnsserver_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateDnsserverRequest classique
        $updateRules = (new UpdateDnsserverRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Dnsserver;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateDnsserverRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateDnsserverRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.roles'] = ['sometimes', 'array'];
        $rules['items.*.roles.*'] = ['integer'];

        return $rules;
    }
}
