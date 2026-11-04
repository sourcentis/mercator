<?php

namespace App\Http\Requests;

use App\Models\Workstation;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateWorkstationRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('workstation_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateWorkstationRequest classique
        $updateRules = (new UpdateWorkstationRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Workstation;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateWorkstationRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateWorkstationRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
