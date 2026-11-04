<?php

namespace App\Http\Requests;

use App\Models\PhysicalSecurityDevice;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdatePhysicalSecurityDeviceRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('physical_security_device_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdatePhysicalSecurityDeviceRequest classique
        $updateRules = (new UpdatePhysicalSecurityDeviceRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new PhysicalSecurityDevice;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdatePhysicalSecurityDeviceRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdatePhysicalSecurityDeviceRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
