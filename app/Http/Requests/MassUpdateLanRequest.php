<?php

namespace App\Http\Requests;

use App\Models\Lan;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateLanRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('lan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateLanRequest classique
        $updateRules = (new UpdateLanRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Lan;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateLanRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateLanRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.mans'] = ['sometimes', 'array'];
        $rules['items.*.mans.*'] = ['integer'];

        $rules['items.*.wans'] = ['sometimes', 'array'];
        $rules['items.*.wans.*'] = ['integer'];

        return $rules;
    }
}
