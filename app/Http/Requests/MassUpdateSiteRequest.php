<?php

namespace App\Http\Requests;

use App\Models\Site;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateSiteRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('site_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateSiteRequest classique
        $updateRules = (new UpdateSiteRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Site;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateSiteRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateSiteRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
