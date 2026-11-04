<?php

namespace App\Http\Requests;

use App\Models\ApplicationFlow;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateApplicationFlowRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('application_flow_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateApplicationFlowRequest classique
        $updateRules = (new UpdateApplicationFlowRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new ApplicationFlow;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateApplicationFlowRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateApplicationFlowRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
