<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesMassSavedQueryDsl;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassStoreSavedQueryRequest extends BaseMassFormRequest
{
    use ValidatesMassSavedQueryDsl;

    public function authorize(): true
    {
        abort_if(Gate::denies('query_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        // On récupère les règles du StoreSecurityControlRequest classique
        $storeRules = new StoreSavedQueryRequest()->rules();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
        ];

        // On applique les règles du StoreSecurityControlRequest à chaque item : items.*.field
        foreach ($storeRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
