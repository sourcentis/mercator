<?php

namespace App\Http\Requests;

use App\Models\Operation;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateOperationRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('operation_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateOperationRequest classique
        $updateRules = (new UpdateOperationRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Operation;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateOperationRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateOperationRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.activities'] = ['sometimes', 'array'];
        $rules['items.*.activities.*'] = ['integer'];

        $rules['items.*.actors'] = ['sometimes', 'array'];
        $rules['items.*.actors.*'] = ['integer'];

        $rules['items.*.tasks'] = ['sometimes', 'array'];
        $rules['items.*.tasks.*'] = ['integer'];

        return $rules;
    }
}
