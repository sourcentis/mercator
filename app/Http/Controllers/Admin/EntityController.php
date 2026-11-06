<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyEntityRequest;
use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;
use App\Models\Application;
use App\Models\Cartographer;
use App\Models\Database;
use App\Models\Entity;
use App\Models\Process;
use App\Services\IconUploadService;
use Gate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EntityController extends Controller
{
    public function __construct(private readonly IconUploadService $iconUploadService) {}

    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('entity_access') ? null : Cartographer::allowedIdsFor($user, Entity::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $entities = Entity::query()
            ->with('processes')
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Entity::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')

            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))
            ->paginate($this->resolvePerPage());

        return view('admin.entities.index', compact('entities'));
    }

    public function create()
    {
        abort_if(Gate::denies('entity_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $processes = Process::query()->orderBy('name')->pluck('name', 'id');
        $applications = Application::query()->orderBy('name')->pluck('name', 'id');
        $databases = Database::query()->orderBy('name')->pluck('name', 'id');
        $entityTypes = Entity::query()->select('type')
            ->where('type', '<>', null)->distinct()
            ->orderBy('type')->pluck('type');
        $entities = Entity::query()->orderBy('name')->pluck('name', 'id');
        $icons = Entity::query()->select('icon_id')->whereNotNull('icon_id')->orderBy('icon_id')->distinct()->pluck('icon_id');
        $attributes_list = $this->getAttributes();

        return view(
            'admin.entities.create',
            compact('processes', 'entityTypes', 'applications', 'databases', 'entities', 'icons', 'attributes_list')
        );
    }

    public function store(StoreEntityRequest $request)
    {
        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $parentId = $request->input('parent_entity_id') ?: null;
        $childrenIds = $request->input('childEntities', []);

        if ($this->wouldCreateCycle(null, $parentId, $childrenIds)) {
            $errorField = $parentId !== null ? 'parent_entity_id' : 'childEntities';

            return redirect()
                ->back()
                ->withInput()
                ->withErrors([$errorField => trans('cruds.entity.errors.cycle_detected')]);
        }

        $entity = Entity::query()->create($request->all());

        // Save icon
        $this->iconUploadService->handle($request, $entity);

        // Save entity
        $entity->save();

        // Save relations
        $entity->processes()->sync($request->input('processes', []));

        // update applications table
        Application::query()->whereIn('id', $request->input('respApplications', []))
            ->update(['entity_resp_id' => $entity->id, 'updated_by' => Auth::id()]);

        // update databases table
        Database::query()->whereIn('id', $request->input('databases', []))
            ->update(['entity_resp_id' => $entity->id, 'updated_by' => Auth::id()]);

        // update child entities
        Entity::query()->whereIn('id', $request->input('childEntities', []))
            ->where('id', '!=', $entity->id)
            ->update(['parent_entity_id' => $entity->id, 'updated_by' => Auth::id()]);

        return redirect()->route('admin.entities.index');
    }

    public function edit(Entity $entity)
    {
        abort_if(Gate::denies('edit-object', $entity), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $processes = Process::query()->orderBy('name')->pluck('name', 'id');
        $applications = Application::query()->orderBy('name')->pluck('name', 'id');
        $databases = Database::query()->orderBy('name')->pluck('name', 'id');
        $entityTypes = Entity::query()->select('type')
            ->where('type', '<>', null)->distinct()
            ->orderBy('type')->pluck('type');
        $entity->load('processes', 'applications', 'databases', 'entities');
        $entities = Entity::query()->orderBy('name')->pluck('name', 'id');
        $icons = Entity::query()->select('icon_id')->whereNotNull('icon_id')->orderBy('icon_id')->distinct()->pluck('icon_id');
        $attributes_list = $this->getAttributes();

        return view(
            'admin.entities.edit',
            compact('entity', 'entityTypes', 'processes', 'applications', 'databases', 'entities', 'icons', 'attributes_list')
        );
    }

    public function update(UpdateEntityRequest $request, Entity $entity)
    {
        abort_if(Gate::denies('edit-object', $entity), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $newParentId = $request->input('parent_entity_id') ?: null;
        $childrenIds = $request->input('childEntities', []);

        if ($this->wouldCreateCycle($entity->id, $newParentId, $childrenIds)) {
            $errorField = $newParentId !== null ? 'parent_entity_id' : 'childEntities';

            return redirect()
                ->back()
                ->withInput()
                ->withErrors([$errorField => trans('cruds.entity.errors.cycle_detected')]);
        }

        // Save icon
        $this->iconUploadService->handle($request, $entity);

        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        // Update fields
        $entity->update($request->all());

        // Save relations
        $entity->processes()->sync($request->input('processes', []));

        // update applications table
        Application::query()->where('entity_resp_id', $entity->id)
            ->update(['entity_resp_id' => null, 'updated_by' => Auth::id()]);

        Application::query()->whereIn('id', $request->input('respApplications', []))
            ->update(['entity_resp_id' => $entity->id, 'updated_by' => Auth::id()]);

        // update databases table
        Database::query()->where('entity_resp_id', $entity->id)
            ->update(['entity_resp_id' => null, 'updated_by' => Auth::id()]);

        Database::query()->whereIn('id', $request->input('databases', []))
            ->update(['entity_resp_id' => $entity->id, 'updated_by' => Auth::id()]);

        // update child entities
        Entity::query()->where('parent_entity_id', $entity->id)
            ->update(['parent_entity_id' => null, 'updated_by' => Auth::id()]);

        Entity::query()->whereIn('id', $request->input('childEntities', []))
            ->where('id', '!=', $entity->id)
            ->update(['parent_entity_id' => $entity->id, 'updated_by' => Auth::id()]);

        return redirect()->route('admin.entities.index');
    }

    public function show(Entity $entity)
    {
        abort_if(Gate::denies('show-object', $entity), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $entity->load('databases', 'applications', 'sourceRelations', 'destinationRelations', 'respApplications', 'processes', 'entities');

        return view('admin.entities.show', compact('entity'));
    }

    public function destroy(Entity $entity)
    {
        abort_if(Gate::denies('entity_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $entity->delete();

        return redirect()->route('admin.entities.index');
    }

    public function massDestroy(MassDestroyEntityRequest $request)
    {
        Entity::query()->whereIn('id', request('ids'))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function getAttributes()
    {
        $attributes_list = Entity::query()
            ->select('attributes')
            ->where('attributes', '<>', null)
            ->pluck('attributes');
        $res = [];
        foreach ($attributes_list as $i) {
            foreach (explode(' ', $i) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        sort($res);

        return array_unique($res);
    }

    /**
     * Charge toutes les entités en mémoire (cache local), indexées par ID, pour
     * pouvoir remonter la chaîne des parents sans requêter la base à chaque étape.
     *
     * @return Collection<int, Entity>
     */
    private function getAllEntities(): Collection
    {
        return Entity::withTrashed()
            ->select('id', 'parent_entity_id')
            ->get()
            ->keyBy('id');
    }

    /**
     * Vérifie si l'affectation d'un parent et/ou d'enfants à une entité créerait un
     * cycle dans la hiérarchie (une entité ne peut pas être son propre ancêtre).
     *
     * Construit l'état final proposé (parent de cette entité + parent de chaque
     * enfant sélectionné) et remonte, pour chaque nœud modifié, la chaîne des
     * parents résultante : si un nœud déjà visité est retrouvé, il y a un cycle.
     * Cela couvre aussi bien un cycle direct (auto-parent/auto-enfant) qu'un cycle
     * indirect via un ancêtre/descendant lointain, ou une interaction entre le
     * changement de parent et celui des enfants dans la même requête.
     *
     * @param  int|null  $entityId  ID de l'entité concernée (null si en cours de création)
     * @param  int|null  $parentId  ID du parent proposé pour cette entité
     * @param  array<int, int|string>  $childrenIds  IDs des entités devant devenir enfants de celle-ci
     */
    private function wouldCreateCycle(?int $entityId, ?int $parentId, array $childrenIds): bool
    {
        $entities = $this->getAllEntities();

        // Une entité en cours de création n'a pas encore d'ID réel : on utilise un
        // identifiant sentinelle qui ne peut correspondre à aucune ligne existante.
        $selfId = $entityId ?? 0;

        $overrides = [$selfId => $parentId];
        foreach ($childrenIds as $childId) {
            $overrides[(int) $childId] = $selfId;
        }

        $resolveParent = fn (int $id): ?int => array_key_exists($id, $overrides)
            ? $overrides[$id]
            : $entities->get($id)?->parent_entity_id;

        foreach (array_keys($overrides) as $node) {
            $visited = [$node => true];
            $current = $resolveParent($node);

            while ($current !== null) {
                if (isset($visited[$current])) {
                    return true;
                }
                $visited[$current] = true;
                $current = $resolveParent($current);
            }
        }

        return false;
    }
}
