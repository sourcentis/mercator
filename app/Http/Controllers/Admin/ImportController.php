<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseFormRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ImportController extends Controller
{
    public function show(): View
    {
        return view('admin/import');
    }

    /**
     * @throws \ReflectionException
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export(Request $request): BinaryFileResponse
    {
        Log::info('Export - Start');

        $request->validate([
            'object' => 'required',
        ]);

        // Model name from request
        $modelName = $request->get('object');

        Log::info("Export - {$modelName}");

        // Check permission
        abort_if(Gate::denies($this->permission($modelName, 'access')), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Get class
        $modelClass = $this->resolveModelClass($modelName);

        // Récupération brute des enregistrements
        $items = $modelClass::all();

        Log::info("Export - count : {$items->count()}");

        $data = [];
        foreach ($items as $item) {
            $row = $item->toArray();

            // Traite les belongsToMany : transforme en liste d'IDs
            foreach ((new ReflectionClass($item))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                // On évite les méthodes héritées de Model ou autres (ex: getKey, save, etc.)
                if ($method->class !== $item::class) {
                    continue;
                }
                if ($method->getNumberOfParameters() !== 0) {
                    continue;
                }
                $returnType = $method->getReturnType();
                if (! $returnType instanceof \ReflectionNamedType) {
                    continue;
                }
                try {
                    $result = $method->invoke($item);
                    if ($result instanceof BelongsToMany) {
                        $relationName = $method->getName();
                        $row[$relationName] = $item->$relationName()->pluck('id')->implode(', ');
                    }
                } catch (\Throwable $e) {
                    // Ignore toute méthode non relationnelle qui lancerait une erreur
                    continue;
                }
            }

            // Exclure les colonnes inutiles
            unset($row['created_at'], $row['updated_at'], $row['deleted_at']);
            unset($row['created_by'], $row['updated_by'], $row['deleted_by']);

            $data[] = $row;
        }

        Log::info('Export - Done.');

        // Get header
        $header = array_keys($data[0] ?? []);

        return Excel::download(new GenericExport($data, $header), $modelName.'-'.Carbon::today()->format('Ymd').'.xlsx')
            ->deleteFileAfterSend(true);
    }

    public static function permission($modelName, $action): string
    {
        return Str::snake($modelName, '_').'_'.$action;
    }

    /**
     * @throws \Throwable
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
            'object' => 'required',
        ]);

        $modelName = $request->get('object');
        $modelClass = $this->resolveModelClass($modelName);

        // Get store validation rules
        $storeRequestClass = '\\App\\Http\\Requests\\Store'.$modelName.'Request';
        $storeRequestInstance = new $storeRequestClass;
        $storeRules = $storeRequestInstance->rules();

        // Get update validation rules
        $updateRequestClass = '\\App\\Http\\Requests\\Update'.$modelName.'Request';
        $updateRequestInstance = new $updateRequestClass;

        abort_if(Gate::denies($this->permission($modelName, 'edit')), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $deleteCount = 0;
        $insertCount = 0;
        $updateCount = 0;
        $simulatedErrors = [];

        $rows = Excel::toCollection((object) null, $request->file('file'))->first();
        $header = $rows->shift();

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowData = $header->combine($row);
                $id = $rowData->get('id');

                try {
                    $attributes = $rowData->except('id');
                    if ($storeRequestInstance instanceof BaseFormRequest) {
                        $attributes = collect($storeRequestInstance->sanitizeAttributes($attributes->all()));
                    }
                    $relations = [];

                    // Identify relations and remove them from attributes
                    foreach ($attributes as $key => $value) {
                        if (! method_exists($modelClass, $key)) {
                            continue;
                        }

                        try {
                            $relationInstance = (new $modelClass)->{$key}();
                            if ($relationInstance instanceof BelongsToMany) {
                                $relations[$key] = array_filter(array_map('trim', explode(',', $value)));
                                $attributes->forget($key);
                            }
                        } catch (\Throwable $e) {
                            // ignorer si ce n'est pas une relation
                        }
                    }
                    if ($id && $rowData->filter()->count() === 1) {
                        // Delete
                        $record = $modelClass::find($id);
                        if ($record) {
                            if (Gate::denies($this->permission($modelName, 'delete'), $record)) {
                                throw new \Exception("record {$id} : 403 Forbidden");
                            }
                            $record->delete();
                            $deleteCount++;
                        }
                    } elseif (! $id) {
                        // Create
                        $validationAttributes = clone $attributes;
                        $this->normalizeArrayFields($validationAttributes, $storeRules);
                        $validator = Validator::make($validationAttributes->toArray(), $storeRules);
                        if ($validator->fails()) {
                            throw new \Exception(implode(', ', $validator->errors()->all()));
                        }
                        $record = $modelClass::create($attributes->toArray());
                        foreach ($relations as $rel => $ids) {
                            if ($rowData->has($rel) && ! empty($ids)) {
                                $record->{$rel}()->sync($ids);
                            }
                        }
                        $insertCount++;
                    } else {
                        // Update
                        $record = $modelClass::find($id);
                        if ($record) {
                            if (Gate::denies('edit-object', $record)) {
                                throw new \Exception("record {$id} : 403 Forbidden");
                            }
                            $updateRequestInstance->id = $id;
                            $updateRules = $updateRequestInstance->rules();
                            $validationAttributes = clone $attributes;
                            $this->normalizeArrayFields($validationAttributes, $updateRules);
                            $validator = Validator::make($validationAttributes->toArray(), $updateRules);
                            if ($validator->fails()) {
                                throw new \Exception(implode(', ', $validator->errors()->all()));
                            }
                            $record->update($attributes->toArray());
                            foreach ($relations as $rel => $ids) {
                                if ($rowData->has($rel)) {
                                    $record->{$rel}()->sync($ids);
                                }
                            }
                            $updateCount++;
                        } else {
                            throw new \Exception("record {$id} not found");
                        }
                    }
                } catch (\Throwable $e) {
                    $simulatedErrors[] = 'Ligne '.($index + 2).': '.$e->getMessage();
                    if (count($simulatedErrors) >= 10) {
                        break;
                    }
                }
            }

            if (count($simulatedErrors)) {
                DB::rollBack();

                return back()->withInput()->withErrors($simulatedErrors);
            }

            DB::commit();

            return back()->withInput()
                ->withMessage("Success : {$insertCount} inserted, {$updateCount} updated, {$deleteCount} deleted");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    /**
     * Certains champs (ex: attributes) sont validés comme tableau côté formulaire web
     * mais exportés/stockés sous forme de chaîne (valeurs séparées par des espaces).
     * On les reconvertit en tableau avant validation lors d'un import.
     */
    private function normalizeArrayFields(Collection $attributes, array $rules): void
    {
        foreach ($rules as $field => $rule) {
            if (! $attributes->has($field)) {
                continue;
            }

            $ruleParts = is_array($rule) ? $rule : explode('|', (string) $rule);
            if (! in_array('array', $ruleParts, true)) {
                continue;
            }

            $value = $attributes->get($field);
            if (is_array($value)) {
                continue;
            }

            $value = trim((string) $value);
            $attributes->put($field, $value === '' ? [] : array_values(array_filter(explode(' ', $value), fn ($v) => $v !== '')));
        }
    }

    private function resolveModelClass($modelName)
    {
        $modelClass = 'App\\Models\\'.$modelName;

        Log::info("Import - {$modelClass}");

        if (! class_exists($modelClass)) {
            abort(404, "Modèle [{$modelName}] introuvable.");
        }

        Log::info("Import - {$modelClass} found !");

        return $modelClass;
    }
}

/**
 * Classe d'export Excel générique.
 */
class GenericExport implements FromArray, WithHeadings, WithStyles
{
    protected array $data;

    protected array $headers;

    public function __construct(array $data, array $headers)
    {
        $this->data = $data;
        $this->headers = $headers;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headers;
    }

    public function styles(Worksheet $sheet)
    {
        $columnCount = count($this->headers);
        $endColumn = Coordinate::stringFromColumnIndex($columnCount);
        $headerRange = 'A1:'.$endColumn.'1';

        return [
            $headerRange => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '94e5ff'],
                ],
            ],
        ];
    }
}
