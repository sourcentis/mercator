<?php

namespace App\Http\Requests;

use App\Models\Information;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateInformationRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('information_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateInformationRequest classique
        $updateRules = (new UpdateInformationRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Information;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateInformationRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateInformationRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.processes'] = ['sometimes', 'array'];
        $rules['items.*.processes.*'] = ['integer'];

        return $rules;
    }
}
