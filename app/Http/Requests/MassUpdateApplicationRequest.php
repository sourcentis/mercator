<?php

namespace App\Http\Requests;

use App\Models\Application;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateApplicationRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('application_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateApplicationRequest classique
        $updateRules = (new UpdateApplicationRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Application;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],

            // l'id n'est pas dans UpdateApplicationRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateApplicationRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        // Relations many-to-many / attach par IDs
        $rules['items.*.entities'] = ['sometimes', 'array'];
        $rules['items.*.entities.*'] = ['integer'];

        $rules['items.*.processes'] = ['sometimes', 'array'];
        $rules['items.*.processes.*'] = ['integer'];

        $rules['items.*.activities'] = ['sometimes', 'array'];
        $rules['items.*.activities.*'] = ['integer'];

        $rules['items.*.databases'] = ['sometimes', 'array'];
        $rules['items.*.databases.*'] = ['integer'];

        $rules['items.*.logical_servers'] = ['sometimes', 'array'];
        $rules['items.*.logical_servers.*'] = ['integer'];

        $rules['items.*.application_services'] = ['sometimes', 'array'];
        $rules['items.*.application_services.*'] = ['integer'];

        return $rules;
    }
}
