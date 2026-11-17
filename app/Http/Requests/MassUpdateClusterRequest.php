<?php

namespace App\Http\Requests;

use App\Models\Cluster;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateClusterRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('cluster_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateClusterRequest classique
        $updateRules = (new UpdateClusterRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Cluster;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateClusterRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateClusterRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.applications'] = ['sometimes', 'array'];
        $rules['items.*.applications.*'] = ['integer'];

        $rules['items.*.servers'] = ['sometimes', 'array'];
        $rules['items.*.servers.*'] = ['integer'];

        return $rules;
    }
}
