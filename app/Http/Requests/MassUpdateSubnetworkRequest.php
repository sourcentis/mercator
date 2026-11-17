<?php

namespace App\Http\Requests;

use App\Models\Subnetwork;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateSubnetworkRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('subnetwork_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateSubnetworkRequest classique
        $updateRules = (new UpdateSubnetworkRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Subnetwork;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateSubnetworkRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateSubnetworkRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
