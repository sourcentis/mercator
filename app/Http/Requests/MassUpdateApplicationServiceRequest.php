<?php

namespace App\Http\Requests;

use App\Models\ApplicationService;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateApplicationServiceRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('application_service_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateApplicationServiceRequest classique
        $updateRules = (new UpdateApplicationServiceRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new ApplicationService;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateApplicationServiceRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateApplicationServiceRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.applications'] = ['sometimes', 'array'];
        $rules['items.*.applications.*'] = ['integer'];

        $rules['items.*.modules'] = ['sometimes', 'array'];
        $rules['items.*.modules.*'] = ['integer'];

        return $rules;
    }
}
