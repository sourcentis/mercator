<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Purge de la base de données :
 *  - suppression des entrées du journal d'audit plus anciennes que la durée de conservation ;
 *  - suppression définitive des objets supprimés (soft delete) depuis plus longtemps que la durée de conservation.
 *
 * Les suppressions passent par le query builder (pas d'événements Eloquent) : la purge
 * ne génère donc pas elle-même d'entrées d'audit et ignore les scopes globaux (périmètres).
 */
class DatabaseCleanupService
{
    /** Modèles soft-deletés exclus de la purge (comptes et rôles restent dans la corbeille). */
    private const EXCLUDED_MODELS = [User::class, Role::class];

    private const CHUNK = 200;

    /** Nombre maximal de passes : un objet purgé peut débloquer un objet traité plus tôt. */
    private const MAX_PASSES = 5;

    /** @var array<string, list<array{table: string, column: string, action: string}>>|null */
    private ?array $references = null;

    /**
     * Supprime les entrées d'audit antérieures à la date limite.
     */
    public function purgeAuditLogs(Carbon $before, bool $dryRun = false): int
    {
        $query = DB::table('audit_logs')->where('created_at', '<', $before);

        if ($dryRun) {
            return $query->count();
        }

        $total = 0;
        do {
            $ids = (clone $query)->orderBy('id')->limit(1000)->pluck('id');
            if ($ids->isEmpty()) {
                break;
            }
            $total += DB::table('audit_logs')->whereIn('id', $ids)->delete();
        } while ($ids->count() === 1000);

        return $total;
    }

    /**
     * Supprime définitivement les objets dont deleted_at est antérieur à la date limite.
     *
     * @return array{deleted: array<string, int>, skipped: array<string, int>}
     */
    public function purgeDeletedObjects(Carbon $before, bool $dryRun = false): array
    {
        $models = $this->softDeletableModels();
        $deleted = [];
        $skipped = [];

        if ($dryRun) {
            foreach ($models as $table => $model) {
                $count = $this->trashedQuery($model, $before)->count();
                if ($count > 0) {
                    $deleted[$table] = $count;
                }
            }

            return ['deleted' => $deleted, 'skipped' => []];
        }

        for ($pass = 0; $pass < self::MAX_PASSES; $pass++) {
            $progress = false;
            $skipped = [];

            foreach ($models as $table => $model) {
                $lastId = 0;
                while (true) {
                    $ids = $this->trashedQuery($model, $before)
                        ->where('id', '>', $lastId)
                        ->orderBy('id')
                        ->limit(self::CHUNK)
                        ->pluck('id')
                        ->all();
                    if ($ids === []) {
                        break;
                    }
                    $lastId = end($ids);

                    [$ok, $failed] = $this->purgeChunk($table, $ids);
                    if ($ok > 0) {
                        $deleted[$table] = ($deleted[$table] ?? 0) + $ok;
                        $progress = true;
                    }
                    if ($failed > 0) {
                        $skipped[$table] = ($skipped[$table] ?? 0) + $failed;
                    }
                }
            }

            if (! $progress || $skipped === []) {
                break;
            }
        }

        return ['deleted' => $deleted, 'skipped' => $skipped];
    }

    /**
     * Modèles de app/Models utilisant SoftDeletes, indexés par nom de table.
     *
     * @return array<string, Model>
     */
    public function softDeletableModels(): array
    {
        $models = [];
        foreach (glob(app_path('Models').'/*.php') as $file) {
            $class = 'App\\Models\\'.basename($file, '.php');
            if (! class_exists($class)
                || ! is_subclass_of($class, Model::class)
                || in_array($class, self::EXCLUDED_MODELS, true)
                || ! in_array(SoftDeletes::class, class_uses_recursive($class), true)) {
                continue;
            }
            /** @var Model $model */
            $model = new $class;
            $models[$model->getTable()] = $model;
        }
        ksort($models);

        return $models;
    }

    private function trashedQuery(Model $model, Carbon $before)
    {
        /** @phpstan-ignore method.notFound */
        $column = $model->getDeletedAtColumn();

        return DB::table($model->getTable())
            ->whereNotNull($column)
            ->where($column, '<', $before);
    }

    /**
     * Purge un lot d'identifiants ; en cas d'échec (contrainte bloquante),
     * reprend objet par objet pour isoler ceux qui ne peuvent pas encore être supprimés.
     *
     * @param  list<int>  $ids
     * @return array{0: int, 1: int} [supprimés, ignorés]
     */
    private function purgeChunk(string $table, array $ids): array
    {
        try {
            return [$this->deleteRows($table, $ids), 0];
        } catch (QueryException) {
            // On isole les lignes fautives.
        }

        $ok = 0;
        $failed = 0;
        foreach ($ids as $id) {
            try {
                $ok += $this->deleteRows($table, [$id]);
            } catch (QueryException $e) {
                $failed++;
                Log::debug("DB cleanup: {$table}#{$id} skipped: ".$e->getMessage());
            }
        }

        return [$ok, $failed];
    }

    /**
     * Supprime les lignes et nettoie les références qui pointent vers elles
     * (voir referencesTo() pour le traitement de chaque référence).
     *
     * @param  list<int>  $ids
     */
    private function deleteRows(string $table, array $ids): int
    {
        return DB::transaction(function () use ($table, $ids) {
            foreach ($this->referencesTo($table) as $ref) {
                if ($ref['action'] === 'delete') {
                    DB::table($ref['table'])->whereIn($ref['column'], $ids)->delete();
                } elseif ($ref['action'] === 'nullify') {
                    DB::table($ref['table'])->whereIn($ref['column'], $ids)->update([$ref['column'] => null]);
                }
            }

            $count = DB::table($table)->whereIn('id', $ids)->delete();

            if ($table === 'documents') {
                foreach ($ids as $id) {
                    $path = storage_path('docs/'.$id);
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }
            }

            return $count;
        });
    }

    /**
     * Clés étrangères (mono-colonne) qui pointent vers la table donnée, avec l'action à appliquer
     * avant de supprimer les lignes référencées :
     *  - `nullify` : colonne nullable, la référence est remise à NULL (l'objet qui pointe reste) ;
     *  - `delete`  : table pivot (sans colonne id) ou ligne dépendante d'une table non soft-deletable
     *                (ex. application_events), supprimée avec son parent ;
     *  - `none`    : référence obligatoire depuis un objet métier, laissée à la base
     *                (CASCADE, ou échec si la contrainte est bloquante : l'objet est alors ignoré).
     *
     * @return list<array{table: string, column: string, action: string}>
     */
    private function referencesTo(string $table): array
    {
        if ($this->references === null) {
            $this->references = [];
            $businessTables = array_keys($this->softDeletableModels());
            $businessTables[] = (new User)->getTable();
            $businessTables[] = (new Role)->getTable();
            $tables = array_unique(Schema::getTableListing(Schema::getCurrentSchemaListing(), false));

            foreach ($tables as $source) {
                $foreignKeys = Schema::getForeignKeys($source);
                if ($foreignKeys === []) {
                    continue;
                }
                $columns = collect(Schema::getColumns($source))->keyBy('name');
                $pivot = ! $columns->has('id');

                foreach ($foreignKeys as $fk) {
                    if (count($fk['columns']) !== 1) {
                        continue;
                    }
                    $column = $fk['columns'][0];
                    $action = match (true) {
                        $pivot => 'delete',
                        (bool) ($columns[$column]['nullable'] ?? false) => 'nullify',
                        ! in_array($source, $businessTables, true) => 'delete',
                        default => 'none',
                    };
                    $this->references[$fk['foreign_table']][] = [
                        'table' => $source,
                        'column' => $column,
                        'action' => $action,
                    ];
                }
            }
        }

        return $this->references[$table] ?? [];
    }
}
