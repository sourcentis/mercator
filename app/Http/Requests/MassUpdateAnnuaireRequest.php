<?php

namespace App\Http\Requests;

use App\Models\Annuaire;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateAnnuaireRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('annuaire_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateAnnuaireRequest classique
        $updateRules = (new UpdateAnnuaireRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Annuaire;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateAnnuaireRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateAnnuaireRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
