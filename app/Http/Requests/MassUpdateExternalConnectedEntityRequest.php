<?php

namespace App\Http\Requests;

use App\Models\ExternalConnectedEntity;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateExternalConnectedEntityRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('external_connected_entity_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateExternalConnectedEntityRequest classique
        $updateRules = (new UpdateExternalConnectedEntityRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new ExternalConnectedEntity;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateExternalConnectedEntityRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateExternalConnectedEntityRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.roles'] = ['sometimes', 'array'];
        $rules['items.*.roles.*'] = ['integer'];

        return $rules;
    }
}
