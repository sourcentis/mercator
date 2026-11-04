<?php

namespace App\Http\Requests;

use App\Models\ApplicationModule;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateApplicationModuleRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('application_module_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateApplicationModuleRequest classique
        $updateRules = (new UpdateApplicationModuleRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new ApplicationModule;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateApplicationModuleRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateApplicationModuleRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
