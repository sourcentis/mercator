<?php

namespace App\Http\Requests;

use App\Models\LogicalServer;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateLogicalServerRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('logical_server_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateLogicalServerRequest classique
        $updateRules = (new UpdateLogicalServerRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new LogicalServer;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateLogicalServerRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateLogicalServerRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.applications'] = ['sometimes', 'array'];
        $rules['items.*.applications.*'] = ['integer'];

        $rules['items.*.databases'] = ['sometimes', 'array'];
        $rules['items.*.databases.*'] = ['integer'];

        $rules['items.*.physicalServers'] = ['sometimes', 'array'];
        $rules['items.*.physicalServers.*'] = ['integer'];

        return $rules;
    }
}
