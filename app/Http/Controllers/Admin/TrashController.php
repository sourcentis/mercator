<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\TrashMode;
use Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Mode corbeille : exploration et restauration des objets supprimés.
 */
class TrashController extends Controller
{
    /**
     * Vue d'ensemble : nombre d'objets supprimés par type, avec un lien vers la liste.
     */
    public function index(): View|RedirectResponse
    {
        if (! TrashMode::active()) {
            return redirect()->route('admin.config.parameters', ['tab' => 'cleanup']);
        }

        // Regroupement par vue, dans l'ordre du menu.
        $routes = TrashMode::indexRoutes();
        $rows = [];
        foreach (TrashMode::MENU as $viewKey => $types) {
            foreach ($types as $class => $labelKey) {
                if (Gate::denies(TrashMode::permissionPrefix($class).'_access')) {
                    continue;
                }
                // Une ligne par liste (ex. graphes : cartes et BPMN)
                foreach (TrashMode::lists($class, $labelKey, $routes) as $list) {
                    $count = $class::onlyTrashed()->where($list['where'])->count();
                    if ($count === 0) {
                        continue;
                    }
                    $rows[trans($viewKey)][] = [
                        'label' => trans($list['label']),
                        'count' => $count,
                        'url' => route($list['route']),
                    ];
                }
            }
        }

        return view('admin.trash.index', ['rows' => $rows]);
    }

    public function enter(): RedirectResponse
    {
        abort_if(Gate::denies('configure'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        session([TrashMode::SESSION_KEY => true]);

        return redirect()->route('admin.trash.index');
    }

    public function exit(): RedirectResponse
    {
        session()->forget(TrashMode::SESSION_KEY);

        return redirect()->route('admin.config.parameters', ['tab' => 'cleanup']);
    }

    public function restore(Request $request, string $type, int $id): RedirectResponse
    {
        $class = TrashMode::modelForKey($type);
        abort_if($class === null, Response::HTTP_NOT_FOUND);
        abort_if(Gate::denies(TrashMode::permissionPrefix($class).'_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /**
         * @var Model $object
         *
         * @phpstan-ignore staticMethod.notFound
         */
        $object = $class::onlyTrashed()->findOrFail($id);
        abort_if(Gate::denies('show-object', $object), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $name = (string) ($object->getAttribute('name') ?? '#'.$object->getKey());

        if ($this->nameIsTaken($object)) {
            return back()->withErrors(trans('cruds.trash.name_taken', ['name' => $name]));
        }

        /** @phpstan-ignore method.notFound */
        $object->restore();

        return back()->with('message', trans('cruds.trash.restored', ['name' => $name]));
    }

    /**
     * Un objet actif du même type porte déjà ce nom (dans le même périmètre) :
     * la restauration créerait un doublon que les formulaires interdisent.
     */
    private function nameIsTaken(Model $object): bool
    {
        $table = $object->getTable();
        $name = $object->getAttribute('name');

        if ($name === null || in_array($object::class, TrashMode::NON_UNIQUE_NAMES, true)
            || ! Schema::hasColumn($table, 'name')) {
            return false;
        }

        return DB::table($table)
            ->whereNull('deleted_at')
            ->where('name', $name)
            ->where('id', '<>', $object->getKey())
            ->when(Schema::hasColumn($table, 'perimeter_id'),
                fn ($q) => $q->where('perimeter_id', $object->getAttribute('perimeter_id')))
            ->exists();
    }
}
