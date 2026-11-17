<?php

namespace App\Http\Requests;

use App\Models\Task;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateTaskRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('task_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateTaskRequest classique
        $updateRules = (new UpdateTaskRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Task;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateTaskRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateTaskRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
