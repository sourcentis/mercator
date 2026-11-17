<?php

namespace App\Http\Requests;

use App\Models\Router;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateRouterRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('router_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateRouterRequest classique
        $updateRules = (new UpdateRouterRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Router;
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
