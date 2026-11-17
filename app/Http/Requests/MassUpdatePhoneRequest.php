<?php

namespace App\Http\Requests;

use App\Models\Phone;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdatePhoneRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('phone_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdatePhoneRequest classique
        $updateRules = (new UpdatePhoneRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Phone;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdatePhoneRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdatePhoneRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
