<?php

namespace App\Http\Requests;

use App\Models\PhysicalRouter;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdatePhysicalRouterRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('physical_router_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdatePhysicalRouterRequest classique
        $updateRules = (new UpdatePhysicalRouterRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new PhysicalRouter;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdatePhysicalRouterRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdatePhysicalRouterRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.vlans'] = ['sometimes', 'array'];
        $rules['items.*.vlans.*'] = ['integer'];

        return $rules;
    }
}
