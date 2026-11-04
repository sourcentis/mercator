<?php

namespace App\Http\Requests;

use App\Models\Entity;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateEntityRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('entity_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateEntityRequest classique
        $updateRules = (new UpdateEntityRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Entity;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateEntityRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateEntityRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.processes'] = ['sometimes', 'array'];
        $rules['items.*.processes.*'] = ['integer'];

        return $rules;
    }
}
