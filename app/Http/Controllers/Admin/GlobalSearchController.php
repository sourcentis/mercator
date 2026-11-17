<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cartographer;
use App\Support\ModelRegistry;
use App\Support\TrashMode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GlobalSearchController extends Controller
{
    /** Whitelist of models offered by the global search — short name => registry-resolved title. */
    private const MODEL_SHORT_NAMES = [
        'Entity', 'Relation', 'Process', 'Operation', 'Actor', 'Activity', 'Task', 'Information',
        'ApplicationBlock', 'Application', 'ApplicationService', 'Database', 'ApplicationFlow',
        'ZoneAdmin', 'Annuaire', 'ForestAd', 'Domain', 'Network', 'Subnetwork', 'Gateway',
        'ExternalConnectedEntity', 'NetworkSwitch', 'Router', 'SecurityDevice', 'DhcpServer',
        'Dnsserver', 'LogicalServer', 'Site', 'Building', 'Bay', 'PhysicalServer', 'Workstation',
        'StorageDevice', 'Peripheral', 'Phone', 'PhysicalSwitch', 'PhysicalRouter', 'WifiTerminal',
        'PhysicalSecurityDevice', 'Wan', 'Man', 'Lan', 'Vlan', 'ApplicationModule', 'MacroProcessus',
        'Certificate', 'DataProcessing', 'SecurityControl', 'LogicalFlow', 'Graph', 'Container', 'Cluster',
    ];

    private array $models;

    public function __construct()
    {
        $this->models = ModelRegistry::titlesMapByShortName(self::MODEL_SHORT_NAMES);
    }

    public function search(Request $request)
    {
        $term = $request->input('search');

        $searchableData = [];

        // La recherche globale est désactivée en mode corbeille.
        if (($term === null) || (mb_strlen($term) < 3) || TrashMode::active()) {
            return view('admin.search', compact('searchableData'));
        }

        $driver = DB::connection()->getDriverName();

        foreach ($this->models as $model => $title) {
            $modelClass = 'App\\Models\\'.$model;

            $fields = property_exists($modelClass, 'searchable') ? $modelClass::$searchable : [];

            if (empty($fields)) {
                continue;
            }

            $escaped = mb_strtolower($this->escapeLike($term));
            $results = Cartographer::scopedQueryByClass($modelClass)
                ->where(function ($q) use ($fields, $escaped, $driver) {
                    foreach ($fields as $field) {
                        // SQLite n'a aucun caractère d'échappement LIKE par défaut :
                        // il faut le déclarer. MySQL/MariaDB/PostgreSQL utilisent '\'
                        // par défaut, inutile de le répéter.
                        $sql = 'LOWER('.$field.') LIKE ?';
                        if ($driver === 'sqlite') {
                            $sql .= " ESCAPE '\\'";
                        }
                        $q->orWhereRaw($sql, ['%'.$escaped.'%']);
                    }
                })
                ->take(100)
                ->get();

            $formattedFields = [];
            foreach ($fields as $field) {
                $formattedFields[$field] = Str::title(str_replace('_', ' ', $field));
            }

            foreach ($results as $result) {
                $searchableData[] = [
                    'instance' => $result,
                    'data' => $result->only($fields),
                    'model' => $model,
                    'name' => $title,
                    'fields' => $fields,
                    'fields_formated' => $formattedFields,
                    'url' => '/admin/'.ModelRegistry::slug($model).'/'.$result->getKey(),
                ];
            }
        }

        return view('admin.search', compact('searchableData'));
    }

    private function escapeLike(string $value): string
    {
        // '=' comme caractère d'échappement (neutre dans les littéraux SQL des 4 moteurs).
        // Le caractère d'échappement lui-même doit être échappé en premier.
        return str_replace(['=', '%', '_'], ['==', '=%', '=_'], $value);
    }
}
