<?php

namespace App\Http\Requests;

use App\Models\Actor;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateActorRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('actor_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateActorRequest classique
        $updateRules = (new UpdateActorRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Actor;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateActorRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateActorRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
