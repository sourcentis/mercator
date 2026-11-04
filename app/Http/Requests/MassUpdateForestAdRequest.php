<?php

namespace App\Http\Requests;

use App\Models\ForestAd;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateForestAdRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('forest_ad_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateForestAdRequest classique
        $updateRules = (new UpdateForestAdRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new ForestAd;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateForestAdRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateForestAdRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
