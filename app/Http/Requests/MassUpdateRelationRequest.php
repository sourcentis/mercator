<?php

namespace App\Http\Requests;

use App\Models\Relation;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateRelationRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('relation_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateRelationRequest classique
        $updateRules = (new UpdateRelationRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Relation;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateRelationRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateRelationRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
