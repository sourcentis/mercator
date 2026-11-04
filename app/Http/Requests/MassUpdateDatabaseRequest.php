<?php

namespace App\Http\Requests;

use App\Models\Database;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateDatabaseRequest extends BaseMassFormRequest
{
    public function authorize(): bool
    {
        abort_if(Gate::denies('database_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        // Règles du UpdateDatabaseRequest classique
        $updateRules = (new UpdateDatabaseRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new Database;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateDatabaseRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateDatabaseRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        $rules['items.*.applications'] = ['sometimes', 'array'];
        $rules['items.*.applications.*'] = ['integer'];

        $rules['items.*.entities'] = ['sometimes', 'array'];
        $rules['items.*.entities.*'] = ['integer'];

        $rules['items.*.informations'] = ['sometimes', 'array'];
        $rules['items.*.informations.*'] = ['integer'];

        $rules['items.*.logical_servers'] = ['sometimes', 'array'];
        $rules['items.*.logical_servers.*'] = ['integer'];

        $rules['items.*.roles'] = ['sometimes', 'array'];
        $rules['items.*.roles.*'] = ['integer'];

        return $rules;
    }
}
