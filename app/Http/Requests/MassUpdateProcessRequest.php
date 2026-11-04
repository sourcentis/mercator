<?php

namespace App\Http\Requests;

use App\Models\Process;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateProcessRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('process_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateProcessRequest classique
        $updateRules = (new UpdateProcessRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Process;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateProcessRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateProcessRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.activities'] = ['sometimes', 'array'];
        $rules['items.*.activities.*'] = ['integer'];

        $rules['items.*.applications'] = ['sometimes', 'array'];
        $rules['items.*.applications.*'] = ['integer'];

        $rules['items.*.entities'] = ['sometimes', 'array'];
        $rules['items.*.entities.*'] = ['integer'];

        $rules['items.*.informations'] = ['sometimes', 'array'];
        $rules['items.*.informations.*'] = ['integer'];

        $rules['items.*.operations'] = ['sometimes', 'array'];
        $rules['items.*.operations.*'] = ['integer'];

        return $rules;
    }
}
