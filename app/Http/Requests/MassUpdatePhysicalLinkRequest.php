<?php

namespace App\Http\Requests;

use App\Models\PhysicalLink;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdatePhysicalLinkRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('physical_link_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdatePhysicalLinkRequest classique
        $updateRules = (new UpdatePhysicalLinkRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new PhysicalLink;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdatePhysicalLinkRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdatePhysicalLinkRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
