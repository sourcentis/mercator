<?php

namespace App\Http\Requests;

use App\Models\PhysicalSwitch;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdatePhysicalSwitchRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('physical_switch_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdatePhysicalSwitchRequest classique
        $updateRules = (new UpdatePhysicalSwitchRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new PhysicalSwitch;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdatePhysicalSwitchRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdatePhysicalSwitchRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
