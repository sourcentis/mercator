<?php

namespace App\Http\Requests;

use App\Models\WifiTerminal;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassUpdateWifiTerminalRequest extends BaseMassFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('wifi_terminal_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        // Règles du UpdateWifiTerminalRequest classique
        $updateRules = (new UpdateWifiTerminalRequest)->rules();

        // On récupère dynamiquement le nom de la table du modèle
        $model = new WifiTerminal;
        $table = $model->getTable();

        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            // l'id n'est pas dans UpdateWifiTerminalRequest (route model binding),
            'items.*.id' => ['required', 'integer', "exists:{$table},id"],
        ];

        // On applique les règles du UpdateWifiTerminalRequest à chaque item : items.*.field
        foreach ($updateRules as $field => $rule) {
            $rules["items.*.$field"] = $rule;
        }

        return $rules;
    }
}
