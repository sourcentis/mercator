<?php

namespace App\Http\Requests;

use App\Models\Graph;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateGraphRequest extends BaseMassFormRequest
{
    /**
     * Le contenu est un document XML (mxGraph / BPMN) : il ne doit pas être passé à strip_tags
     */
    protected array $rawFields = ['content'];

    public function authorize()
    {
        abort_if(Gate::denies('graph_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateGraphRequest classique
        $updateRules = (new UpdateGraphRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Graph;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateGraphRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateGraphRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
