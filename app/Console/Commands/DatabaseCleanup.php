<?php

namespace App\Console\Commands;

use App\Models\Parameter;
use App\Services\DatabaseCleanupService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DatabaseCleanup extends Command
{
    /** Paramètre (table `parameters`) mémorisant le résultat de la dernière exécution. */
    public const LAST_RUN_PARAMETER = 'db_cleanup.last_run';

    protected $signature = 'mercator:db-cleanup
                            {--dry-run : Affiche ce qui serait supprimé sans rien supprimer}';

    protected $description = 'Purge the audit log and permanently delete soft-deleted objects according to the retention settings';

    public function handle(DatabaseCleanupService $service): int
    {
        $cfg = config('mercator.cleanup', []);
        $dryRun = (bool) $this->option('dry-run');

        $logsEnabled = (bool) ($cfg['audit_logs_enabled'] ?? false);
        $objectsEnabled = (bool) ($cfg['deleted_objects_enabled'] ?? false);

        if (! $logsEnabled && ! $objectsEnabled) {
            $this->info('Database cleanup is disabled.');

            return self::SUCCESS;
        }

        $summary = ['date' => now()->toDateTimeString()];

        if ($logsEnabled) {
            $months = max(1, (int) ($cfg['audit_logs_retention'] ?? 12));
            $count = $service->purgeAuditLogs(Carbon::now()->subMonths($months), $dryRun);
            $summary['audit_logs'] = $count;
            $this->info(($dryRun ? '[dry-run] ' : '')."Audit log: {$count} entries older than {$months} month(s) removed.");
        }

        if ($objectsEnabled) {
            $months = max(1, (int) ($cfg['deleted_objects_retention'] ?? 3));
            $result = $service->purgeDeletedObjects(Carbon::now()->subMonths($months), $dryRun);
            $summary['deleted_objects'] = array_sum($result['deleted']);
            $summary['skipped_objects'] = array_sum($result['skipped']);

            foreach ($result['deleted'] as $table => $count) {
                $this->line("  {$table}: {$count}");
            }
            foreach ($result['skipped'] as $table => $count) {
                $this->warn("  {$table}: {$count} skipped (still referenced)");
            }
            $this->info(($dryRun ? '[dry-run] ' : '')
                ."Deleted objects: {$summary['deleted_objects']} purged (deleted more than {$months} month(s) ago)"
                .($summary['skipped_objects'] > 0 ? ", {$summary['skipped_objects']} skipped" : '').'.');
        }

        if (! $dryRun) {
            Parameter::setValue(self::LAST_RUN_PARAMETER, json_encode($summary));
        }

        return self::SUCCESS;
    }
}
