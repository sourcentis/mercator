<?php

namespace App\Http\Requests;

use App\Models\DhcpServer;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateDhcpServerRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('dhcp_server_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateDhcpServerRequest classique
        $updateRules = (new UpdateDhcpServerRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new DhcpServer;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateDhcpServerRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateDhcpServerRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.roles'] = ['sometimes', 'array'];
        $rules['items.*.roles.*'] = ['integer'];

        return $rules;
    }
}
