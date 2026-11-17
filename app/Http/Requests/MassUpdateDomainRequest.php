<?php

namespace App\Http\Requests;

use App\Models\Domain;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateDomainRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('domain_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateDomainRequest classique
        $updateRules = (new UpdateDomainRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Domain;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateDomainRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateDomainRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.forestAds'] = ['sometimes', 'array'];
        $rules['items.*.forestAds.*'] = ['integer'];

        return $rules;
    }
}
