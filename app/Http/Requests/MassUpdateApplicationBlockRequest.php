<?php

namespace App\Http\Requests;

use App\Models\ApplicationBlock;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateApplicationBlockRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('application_block_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateApplicationBlockRequest classique
        $updateRules = (new UpdateApplicationBlockRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new ApplicationBlock;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateApplicationBlockRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateApplicationBlockRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.applications'] = ['sometimes', 'array'];
        $rules['items.*.applications.*'] = ['integer'];

        return $rules;
    }
}
