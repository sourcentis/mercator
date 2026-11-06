<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyBuildingRequest;
use App\Http\Requests\StoreBuildingRequest;
use App\Http\Requests\UpdateBuildingRequest;
use App\Models\Building;
use App\Models\Cartographer;
use App\Models\Site;
use App\Services\IconUploadService;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BuildingController extends Controller
{
    public function __construct(private readonly IconUploadService $iconUploadService) {}

    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('building_access') ? null : Cartographer::allowedIdsFor($user, Building::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $buildings = Building::with([
            'site:id,name',
            'building:id,name',
            'buildings:id,name,building_id',
        ])
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (Building::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')

            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate($this->resolvePerPage());

        return view('admin.buildings.index', compact('buildings'));
    }

    public function create()
    {
        abort_if(Gate::denies('building_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $sites = Site::query()->orderBy('name')->pluck('name', 'id');
        $buildings = Building::query()->orderBy('name')->pluck('name', 'id');
        $buildingSiteMap = Building::pluck('site_id', 'id');

        // Lists
        $attributes_list = $this->getAttributes();
        $type_list = Building::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');

        // Select icons
        $icons = Building::query()->select('icon_id')->whereNotNull('icon_id')->orderBy('icon_id')->distinct()->pluck('icon_id');

        return view(
            'admin.buildings.create',
            compact('sites', 'buildings', 'buildingSiteMap', 'icons', 'attributes_list', 'type_list')
        );
    }

    public function clone(Request $request, Building $building)
    {
        abort_if(Gate::denies('building_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $sites = Site::query()->orderBy('name')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $buildings = Building::query()->orderBy('name')->pluck('name', 'id');
        $buildingSiteMap = Building::pluck('site_id', 'id');

        $attributes_list = $this->getAttributes();
        $type_list = Building::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');

        $icons = Building::query()->select('icon_id')->whereNotNull('icon_id')->orderBy('icon_id')->distinct()->pluck('icon_id');

        $data = $building->only($building->getFillable());

        // Convertir la chaîne "attr1 attr2" en tableau pour old('attributes')
        if (isset($data['attributes']) && is_string($data['attributes'])) {
            $data['attributes'] = array_filter(explode(' ', $data['attributes']));
        }

        $request->merge($data);
        $request->flash();

        return view(
            'admin.buildings.create',
            compact('sites', 'buildings', 'buildingSiteMap', 'icons', 'attributes_list', 'type_list')
        );
    }

    public function store(StoreBuildingRequest $request)
    {
        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        // Résoudre automatiquement le site_id si absent
        if (empty($request->input('site_id')) && ! empty($request->input('building_id'))) {
            $siteId = $this->resolveSiteId($request->input('building_id'));
            if ($siteId !== null) {
                $request->merge(['site_id' => $siteId]);
            }
        }

        // Vérifier les cycles AVANT la création
        $parentId = $request->input('building_id');
        $childrenIds = $request->input('buildings', []);

        if ($this->wouldCreateCycleOnCreation($parentId, $childrenIds)) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['buildings' => trans('cruds.building.errors.cycle_detected')]);
        }

        $building = Building::create($request->all());

        // Save icon
        $this->iconUploadService->handle($request, $building);

        // Save Building
        $building->save();

        // set children
        Building::query()->whereIn('id', $childrenIds)
            ->update(['building_id' => $building->id, 'updated_by' => Auth::id()]);

        return redirect()->route('admin.buildings.index');
    }

    public function edit(Building $building)
    {
        abort_if(Gate::denies('edit-object', $building), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $sites = Site::all()->sortBy('name')->pluck('name', 'id');
        $buildings = Building::all()->sortBy('name')->pluck('name', 'id');
        $buildingSiteMap = Building::pluck('site_id', 'id');
        $attributes_list = $this->getAttributes();
        $type_list = Building::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        // Select icons
        $icons = Building::query()->select('icon_id')->whereNotNull('icon_id')->orderBy('icon_id')->distinct()->pluck('icon_id');

        $building->load('site');

        return view(
            'admin.buildings.edit',
            compact('building', 'sites', 'buildings', 'buildingSiteMap', 'icons', 'attributes_list', 'type_list')
        );
    }

    public function update(UpdateBuildingRequest $request, Building $building)
    {
        abort_if(Gate::denies('edit-object', $building), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $newBuildingId = $request->input('building_id');
        $childrenIds = $request->input('buildings', []);

        // Vérifier les cycles en tenant compte du parent ET des enfants ensemble
        if ($this->wouldCreateCycle($building->id, $newBuildingId, $childrenIds)) {
            $errorField = $newBuildingId !== null ? 'building_id' : 'buildings';

            return redirect()
                ->back()
                ->withInput()
                ->withErrors([$errorField => trans('cruds.building.errors.cycle_detected')]);
        }

        // Résoudre automatiquement le site_id si absent
        if (empty($request->input('site_id')) && ! empty($request->input('building_id'))) {
            $siteId = $this->resolveSiteId($request->input('building_id'));
            if ($siteId !== null) {
                $request->merge(['site_id' => $siteId]);
            }
        }

        // Clear building_id if the building is not present
        if (! $request->has('building_id')) {
            $building->building_id = null;
        }

        // Save icon
        $this->iconUploadService->handle($request, $building);

        // Save Building
        $building->update($request->all());

        // update children
        Building::query()->where('building_id', $building->id)
            ->update(['building_id' => null, 'updated_by' => Auth::id()]);

        Building::query()->whereIn('id', $childrenIds)
            ->update(['building_id' => $building->id, 'updated_by' => Auth::id()]);

        return redirect()->route('admin.buildings.index');
    }

    public function show(Building $building)
    {
        abort_if(Gate::denies('show-object', $building), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $building->load('site', 'bays', 'physicalServers', 'workstations', 'storageDevices', 'peripherals', 'phones', 'physicalSwitches');

        return view('admin.buildings.show', compact('building'));
    }

    public function destroy(Building $building)
    {
        abort_if(Gate::denies('building_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $building->delete();

        // due to soft delete, also set null to all children
        Building::query()
            ->where('building_id', $building->id)
            ->update(['building_id' => null, 'updated_by' => Auth::id()]);

        return redirect()->route('admin.buildings.index');
    }

    public function massDestroy(MassDestroyBuildingRequest $request)
    {
        Building::query()->whereIn('id', request('ids'))->get()->each->delete();

        // due to soft delete, also set null to all children
        Building::query()
            ->whereIn('building_id', request('ids'))
            ->update(['building_id' => null, 'updated_by' => Auth::id()]);

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function getAttributes()
    {
        $attributes_list = Building::query()
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
     * Charge tous les buildings en mémoire (cache local)
     *
     * @return Collection Buildings indexés par ID
     */
    private function getAllBuildings(): Collection
    {
        return Building::withTrashed()
            ->select('id', 'building_id', 'site_id')
            ->get()
            ->keyBy('id');
    }

    /**
     * Récupère tous les ancêtres d'un building en remontant la chaîne
     *
     * @param  int  $buildingId  ID du building de départ
     * @param  Collection  $buildings  Collection de tous les buildings
     * @return array Liste des IDs des ancêtres (le building lui-même inclus)
     */
    private function getAncestors(int $buildingId, Collection $buildings): array
    {
        $ancestors = [];
        $currentId = $buildingId;
        $visited = [];

        while ($currentId !== null) {
            if (in_array($currentId, $visited)) {
                break; // Éviter les boucles infinies
            }

            $visited[] = $currentId;
            $ancestors[] = $currentId;

            $currentBuilding = $buildings->get($currentId);
            $currentId = $currentBuilding?->building_id;
        }

        return $ancestors;
    }

    /**
     * Résout le site_id en remontant la chaîne des buildings parents
     *
     * @param  int  $buildingId  ID du building parent
     * @return int|null ID du site trouvé ou null
     */
    private function resolveSiteId(int $buildingId): ?int
    {
        $buildings = $this->getAllBuildings();
        $ancestors = $this->getAncestors($buildingId, $buildings);

        foreach ($ancestors as $ancestorId) {
            $ancestor = $buildings->get($ancestorId);
            if ($ancestor && $ancestor->site_id !== null) {
                return $ancestor->site_id;
            }
        }

        return null;
    }

    /**
     * Vérifie si une configuration créerait un cycle dans la hiérarchie
     *
     * @param  int  $buildingId  ID du building concerné
     * @param  int|null  $parentId  ID du building parent proposé (null si pas de changement)
     * @param  array  $childrenIds  IDs des buildings enfants proposés
     * @return bool true si un cycle serait créé
     */
    private function wouldCreateCycle(int $buildingId, ?int $parentId = null, array $childrenIds = []): bool
    {
        // Cas simple : un building ne peut pas être son propre parent
        if ($parentId !== null && $buildingId === $parentId) {
            return true;
        }

        // Cas simple : un building ne peut pas être son propre enfant
        if (in_array($buildingId, $childrenIds)) {
            return true;
        }

        // Charger tous les buildings
        $buildings = $this->getAllBuildings();

        // Vérifier le cycle avec le parent
        if ($parentId !== null) {
            // Simuler la hiérarchie : temporairement ignorer le building actuel dans la chaîne
            $ancestors = $this->getAncestorsExcluding($parentId, $buildingId, $buildings);
            if (in_array($buildingId, $ancestors)) {
                return true;
            }
        }

        // Vérifier les cycles avec les enfants
        foreach ($childrenIds as $childId) {
            // Vérifier si l'enfant proposé a le building actuel dans ses ancêtres
            // (en excluant le building actuel pour simuler la nouvelle hiérarchie)
            $childAncestors = $this->getAncestorsExcluding((int) $childId, $buildingId, $buildings);
            if (in_array($buildingId, $childAncestors)) {
                return true;
            }

            // Vérifier aussi si le parent proposé est dans les descendants de l'enfant
            if ($parentId !== null && in_array($parentId, $childAncestors)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Récupère tous les ancêtres d'un building en excluant un building spécifique
     * Cela permet de simuler une nouvelle hiérarchie lors de la vérification
     *
     * @param  int  $buildingId  ID du building de départ
     * @param  int  $excludeId  ID du building à exclure de la chaîne
     * @param  Collection  $buildings  Collection de tous les buildings
     * @return array Liste des IDs des ancêtres (sans le building exclu)
     */
    private function getAncestorsExcluding(int $buildingId, int $excludeId, Collection $buildings): array
    {
        $ancestors = [];
        $currentId = $buildingId;
        $visited = [];

        while ($currentId !== null) {
            if (in_array($currentId, $visited)) {
                break; // Éviter les boucles infinies
            }

            $visited[] = $currentId;

            // Ajouter à la liste sauf si c'est le building à exclure
            if ($currentId !== $excludeId) {
                $ancestors[] = $currentId;
            }

            $currentBuilding = $buildings->get($currentId);

            // Si on arrive au building exclu, on s'arrête (on simule qu'il n'existe pas/plus)
            if ($currentId === $excludeId) {
                break;
            }

            $currentId = $currentBuilding?->building_id;
        }

        return $ancestors;
    }

    /**
     * Vérifie si la création d'un building avec un parent et des enfants créerait un cycle
     *
     * @param  int|null  $parentId  ID du building parent proposé
     * @param  array  $childrenIds  IDs des buildings enfants proposés
     * @return bool true si un cycle serait créé
     */
    private function wouldCreateCycleOnCreation(?int $parentId, array $childrenIds): bool
    {
        if (empty($childrenIds) && $parentId === null) {
            return false;
        }

        // Le parent ne peut pas être dans la liste des enfants
        if ($parentId !== null && in_array($parentId, $childrenIds)) {
            return true;
        }

        $buildings = $this->getAllBuildings();

        // Les enfants ne peuvent pas être des ancêtres du parent
        if ($parentId !== null && ! empty($childrenIds)) {
            $parentAncestors = $this->getAncestors($parentId, $buildings);

            foreach ($childrenIds as $childId) {
                if (in_array($childId, $parentAncestors)) {
                    return true;
                }
            }
        }

        return false;
    }
}
