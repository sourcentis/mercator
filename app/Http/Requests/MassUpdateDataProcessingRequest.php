<?php

namespace App\Http\Requests;

use App\Models\DataProcessing;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateDataProcessingRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('data_processing_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateDataProcessingRequest classique
        $updateRules = (new UpdateDataProcessingRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new DataProcessing;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateDataProcessingRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateDataProcessingRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.applications'] = ['sometimes', 'array'];
        $rules['items.*.applications.*'] = ['integer'];

        $rules['items.*.informations'] = ['sometimes', 'array'];
        $rules['items.*.informations.*'] = ['integer'];

        $rules['items.*.processes'] = ['sometimes', 'array'];
        $rules['items.*.processes.*'] = ['integer'];

        return $rules;
    }
}
