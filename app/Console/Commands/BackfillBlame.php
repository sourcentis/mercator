<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\ModelRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remplit created_by / updated_by / deleted_by à partir de l'historique audit_logs.
 *
 * - created_by : user_id de la première entrée « created » ;
 * - updated_by : user_id de la dernière entrée « created » ou « updated » ;
 * - deleted_by : user_id de la dernière entrée « deleted » (objets supprimés uniquement).
 *
 * N'écrit que dans les colonnes NULL : la commande est idempotente. Les écritures passent par
 * le query builder : ni événement, ni audit, ni modification de updated_at.
 */
class BackfillBlame extends Command
{
    protected $signature = 'mercator:backfill-blame
        {--dry-run : Affiche les compteurs sans rien écrire}
        {--chunk=5000 : Nombre d\'entrées audit_logs lues par lot}';

    protected $description = 'Fill created_by / updated_by / deleted_by from the audit_logs history';

    /**
     * Historical subject_type values that no longer match a model class.
     */
    private const array ALIASES = [
        'App\\Models\\MApplication' => 'applications',
    ];

    private const array COLUMNS = ['created_by', 'updated_by', 'deleted_by'];

    private const int WRITE_BATCH = 500;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunk = max(1, (int) $this->option('chunk'));

        $typeToTable = $this->subjectTypeMap();

        foreach (array_unique($typeToTable) as $table) {
            foreach (self::COLUMNS as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    $this->error("Missing column {$table}.{$column}: run the migrations first.");

                    return self::FAILURE;
                }
            }
        }

        /** @var array<int, int> $existingUsers */
        $existingUsers = User::withTrashed()->pluck('id')->flip()->all();

        // [column][table][id] => ?int user id
        $values = ['created_by' => [], 'updated_by' => [], 'deleted_by' => []];
        /** @var array<string, int> $unknownTypes */
        $unknownTypes = [];
        /** @var array<int, int> $purgedUsers user_id => number of audit entries */
        $purgedUsers = [];

        DB::table('audit_logs')
            ->select('id', 'subject_type', 'subject_id', 'user_id', 'description')
            ->whereIn('description', ['created', 'updated', 'deleted'])
            ->chunkById($chunk, function ($rows) use ($typeToTable, $existingUsers, &$values, &$unknownTypes, &$purgedUsers): void {
                foreach ($rows as $row) {
                    $table = $typeToTable[$row->subject_type] ?? null;
                    if ($table === null) {
                        $unknownTypes[$row->subject_type] = ($unknownTypes[$row->subject_type] ?? 0) + 1;

                        continue;
                    }
                    if ($row->subject_id === null) {
                        continue;
                    }
                    $id = (int) $row->subject_id;

                    $userId = $row->user_id === null ? null : (int) $row->user_id;
                    if ($userId !== null && ! isset($existingUsers[$userId])) {
                        $purgedUsers[$userId] = ($purgedUsers[$userId] ?? 0) + 1;
                        $userId = null;
                    }

                    switch ($row->description) {
                        case 'created':
                            // First "created" entry only
                            if (! array_key_exists($id, $values['created_by'][$table] ?? [])) {
                                $values['created_by'][$table][$id] = $userId;
                            }
                            $values['updated_by'][$table][$id] = $userId;
                            break;
                        case 'updated':
                            $values['updated_by'][$table][$id] = $userId;
                            break;
                        case 'deleted':
                            $values['deleted_by'][$table][$id] = $userId;
                            break;
                    }
                }
            });

        // [table][column] => rows updated
        $summary = [];
        foreach (array_unique($typeToTable) as $table) {
            foreach (self::COLUMNS as $column) {
                $summary[$table][$column] = $this->write($table, $column, $values[$column][$table] ?? [], $dryRun);
            }
            unset($values['created_by'][$table], $values['updated_by'][$table], $values['deleted_by'][$table]);
        }

        $this->report($summary, $unknownTypes, $purgedUsers, $dryRun);

        return self::SUCCESS;
    }

    /**
     * subject_type => table, built from the blameable models plus historical aliases.
     *
     * @return array<string, string>
     */
    private function subjectTypeMap(): array
    {
        $map = [];
        foreach (ModelRegistry::BLAMEABLE_MODELS as $class) {
            $map[$class] = (new $class)->getTable();
        }

        return $map + self::ALIASES;
    }

    /**
     * Writes (or counts, in dry-run) the values of one column, only where it is still NULL.
     *
     * @param  array<int, ?int>  $valuesById
     */
    private function write(string $table, string $column, array $valuesById, bool $dryRun): int
    {
        // Group ids by user; null (system / purged user) is never written
        $idsByUser = [];
        foreach ($valuesById as $id => $userId) {
            if ($userId !== null) {
                $idsByUser[$userId][] = $id;
            }
        }

        $count = 0;
        foreach ($idsByUser as $userId => $ids) {
            foreach (array_chunk($ids, self::WRITE_BATCH) as $batch) {
                $query = DB::table($table)->whereIn('id', $batch)->whereNull($column);
                if ($column === 'deleted_by') {
                    $query->whereNotNull('deleted_at');
                }
                $count += $dryRun ? $query->count() : $query->update([$column => $userId]);
            }
        }

        return $count;
    }

    /**
     * @param  array<string, array<string, int>>  $summary
     * @param  array<string, int>  $unknownTypes
     * @param  array<int, int>  $purgedUsers
     */
    private function report(array $summary, array $unknownTypes, array $purgedUsers, bool $dryRun): void
    {
        if ($dryRun) {
            $this->warn('Dry run: nothing has been written.');
        }

        $rows = [];
        $totals = array_fill_keys(self::COLUMNS, 0);
        foreach ($summary as $table => $counts) {
            if (array_sum($counts) === 0) {
                continue;
            }
            $rows[] = [$table, ...array_values($counts)];
            foreach ($counts as $column => $count) {
                $totals[$column] += $count;
            }
        }
        $rows[] = ['TOTAL', ...array_values($totals)];

        $this->table(['Table', ...self::COLUMNS], $rows);

        if ($unknownTypes !== []) {
            $this->warn('Unknown subject_type (ignored):');
            $this->table(['subject_type', 'entries'], collect($unknownTypes)
                ->map(fn (int $count, string $type) => [$type, $count])->values()->all());
        }

        if ($purgedUsers !== []) {
            $this->warn('user_id of purged users (treated as null):');
            $this->table(['user_id', 'entries'], collect($purgedUsers)
                ->map(fn (int $count, int $userId) => [$userId, $count])->values()->all());
        }
    }
}
