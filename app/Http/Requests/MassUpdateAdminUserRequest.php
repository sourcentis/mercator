<?php

namespace App\Http\Requests;

use App\Models\AdminUser;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateAdminUserRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('admin_user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateAdminUserRequest classique
        $updateRules = (new UpdateAdminUserRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new AdminUser;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateAdminUserRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateAdminUserRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
