<?php

namespace App\Http\Requests;

use App\Models\User;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateUserRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateUserRequest classique
        $updateRules = (new UpdateUserRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new User;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateUserRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateUserRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.roles'] = ['sometimes', 'array'];
        $rules['items.*.roles.*'] = ['integer'];

        return $rules;
    }
}
