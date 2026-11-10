<?php

namespace App\Http\Requests;

use App\Models\SavedQuery;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateSavedQueryRequest extends BaseMassFormRequest
{
    public function authorize(): true
    {
        abort_if(Gate::denies('query_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        // Règles du UpdateRouterRequest classique
        $updateRules = new UpdateSavedQueryRequest()->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new SavedQuery;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateRouterRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateRouterRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.physicalRouters'] = ['sometimes', 'array'];
        $rules['items.*.physicalRouters.*'] = ['integer'];

        return $rules;
    }
}
