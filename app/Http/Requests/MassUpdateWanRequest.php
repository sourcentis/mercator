<?php

namespace App\Http\Requests;

use App\Models\Wan;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateWanRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('wan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateWanRequest classique
        $updateRules = (new UpdateWanRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Wan;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateWanRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateWanRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
