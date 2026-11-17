<?php

namespace App\Http\Requests;

use App\Models\Certificate;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateCertificateRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('certificate_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateCertificateRequest classique
        $updateRules = (new UpdateCertificateRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Certificate;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateCertificateRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateCertificateRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.applications'] = ['sometimes', 'array'];
        $rules['items.*.applications.*'] = ['integer'];

        $rules['items.*.logical_servers'] = ['sometimes', 'array'];
        $rules['items.*.logical_servers.*'] = ['integer'];

        return $rules;
    }
}
