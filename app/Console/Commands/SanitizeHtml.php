<?php

namespace App\Console\Commands;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repasse dans HTMLPurifier les champs riches (CKEditor) déjà enregistrés.
 *
 * Ces champs sont affichés sans échappement ({!! !!}) : ils ne sont sûrs que s'ils ont été
 * assainis à l'enregistrement. Les données saisies avant l'assainissement en entrée
 * (ou importées via Excel avant qu'il n'y soit appliqué) ne l'ont pas été.
 */
class SanitizeHtml extends Command
{
    protected $signature = 'mercator:sanitize-html {--dry-run : Affiche les modifications sans les enregistrer}';

    protected $description = 'Sanitize rich-text (HTML) fields already stored in the database';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $total = 0;

        foreach (self::htmlFieldsByModel() as $modelClass => $fields) {
            $table = (new $modelClass)->getTable();
            if (! Schema::hasTable($table)) {
                continue;
            }
            $fields = array_values(array_filter($fields, fn ($field) => Schema::hasColumn($table, $field)));
            if ($fields === []) {
                continue;
            }

            $count = 0;
            // Accès direct à la table : ni scopes (périmètre, soft delete), ni audit, ni updated_at
            DB::table($table)->select(['id', ...$fields])->orderBy('id')
                ->chunkById(500, function ($rows) use ($table, $fields, $dryRun, &$count) {
                    foreach ($rows as $row) {
                        $changes = [];
                        foreach ($fields as $field) {
                            $value = $row->{$field};
                            // Sans balise, rien à exécuter
                            if (! is_string($value) || ! str_contains($value, '<')) {
                                continue;
                            }
                            $cleaned = clean($value);
                            if ($cleaned !== $value) {
                                $changes[$field] = $cleaned;
                            }
                        }
                        if ($changes === []) {
                            continue;
                        }
                        $count++;
                        if (! $dryRun) {
                            DB::table($table)->where('id', $row->id)->update($changes);
                        }
                    }
                });

            if ($count > 0) {
                $this->line(sprintf('%s : %d %s', $table, $count, $dryRun ? 'to sanitize' : 'sanitized'));
            }
            $total += $count;
        }

        $this->info(sprintf('%d record(s) %s.', $total, $dryRun ? 'to sanitize' : 'sanitized'));

        return self::SUCCESS;
    }

    /**
     * Champs riches par modèle, d'après les $htmlFields des FormRequest Store/Update.
     *
     * @return array<class-string, list<string>>
     */
    public static function htmlFieldsByModel(): array
    {
        $result = [];

        // GLOB_BRACE n'existe pas sous musl (Alpine) : deux glob() distincts
        $files = array_merge(
            glob(app_path('Http/Requests/Store*Request.php')) ?: [],
            glob(app_path('Http/Requests/Update*Request.php')) ?: [],
        );

        foreach ($files as $file) {
            if (! preg_match('/^(?:Store|Update)(\w+)Request$/', basename($file, '.php'), $matches)) {
                continue;
            }
            $requestClass = 'App\\Http\\Requests\\'.basename($file, '.php');
            $modelClass = 'App\\Models\\'.$matches[1];
            if (! class_exists($modelClass) || ! is_subclass_of($requestClass, BaseFormRequest::class)) {
                continue;
            }

            $fields = (new $requestClass)->htmlFields();
            if ($fields !== []) {
                $result[$modelClass] = array_values(array_unique([...($result[$modelClass] ?? []), ...$fields]));
            }
        }

        ksort($result);

        return $result;
    }
}
