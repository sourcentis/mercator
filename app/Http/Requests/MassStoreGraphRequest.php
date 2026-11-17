<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreGraphRequest extends BaseMassFormRequest
{
    /**
     * Le contenu est un document XML (mxGraph / BPMN) : il ne doit pas être passé à strip_tags
     */
    protected array $rawFields = ['content'];

    public function authorize()
    {
        abort_if(Gate::denies('graph_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // On récupère les règles du StoreGraphRequest classique
        $storeRules = (new StoreGraphRequest)->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreGraphRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
