<?php

namespace App\Http\Requests;

use App\Models\MacroProcessus;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateMacroProcessusRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('macro_processus_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateMacroProcessusRequest classique
        $updateRules = (new UpdateMacroProcessusRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new MacroProcessus;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateMacroProcessusRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateMacroProcessusRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.processes'] = ['sometimes', 'array'];
        $rules['items.*.processes.*'] = ['integer'];

        return $rules;
    }
}
