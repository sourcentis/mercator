<?php

namespace App\Http\Requests;

use App\Models\ZoneAdmin;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateZoneAdminRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('zone_admin_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateZoneAdminRequest classique
        $updateRules = (new UpdateZoneAdminRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new ZoneAdmin;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateZoneAdminRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateZoneAdminRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
