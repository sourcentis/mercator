<?php

namespace App\Http\Requests;

use App\Models\Permission;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdatePermissionRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('permission_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdatePermissionRequest classique
        $updateRules = (new UpdatePermissionRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Permission;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdatePermissionRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdatePermissionRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
