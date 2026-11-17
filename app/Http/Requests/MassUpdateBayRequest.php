<?php

namespace App\Http\Requests;

use App\Models\Bay;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateBayRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('bay_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateBayRequest classique
        $updateRules = (new UpdateBayRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Bay;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateBayRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateBayRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
