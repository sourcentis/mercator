<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyRoleRequest;
use App\Http\Requests\MassStoreRoleRequest;
use App\Http\Requests\MassUpdateRoleRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use App\Support\RoleAssignment;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends APIController
{
    protected string $modelClass = Role::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('role_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreRoleRequest $request)
    {
        abort_if(Gate::denies('role_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $role = Role::query()->create($request->all());

        return response()->json($role, Response::HTTP_CREATED);
    }

    public function show(Role $role)
    {
        abort_if(Gate::denies('show-object', $role), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $role['permissions'] = $role->permissions()->pluck('id');

        return new JsonResource($role);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        abort_if(Gate::denies('edit-object', $role), Response::HTTP_FORBIDDEN, '403 Forbidden');
        RoleAssignment::authorizeManageRole($request->user(), $role);
        RoleAssignment::authorizeRolePermissions(
            $request->user(),
            $request->has('permissions')
                ? $request->input('permissions', [])
                : $role->permissions()->pluck('permissions.id')->all(),
            (int) $request->input('perimeter_id', $role->perimeter_id)
        );

        $role->update($request->all());

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->input('permissions', []));
        }

        return response()->json();
    }

    public function destroy(Request $request, Role $role)
    {
        abort_if(Gate::denies('role_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        RoleAssignment::authorizeManageRole($request->user(), $role);

        $role->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyRoleRequest $request)
    {
        abort_if(Gate::denies('role_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Role::whereIn('id', $request->input('ids', []))->get()
            ->each(fn (Role $role) => RoleAssignment::authorizeManageRole($request->user(), $role));

        Role::whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreRoleRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `role_create`

        $createdIds = [];
        $roleModel = new Role;
        $fillable = $roleModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Colonnes du modèle uniquement
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var Role $role */
            $role = Role::query()->create($attributes);

            $createdIds[] = $role->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateRoleRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `role_edit`
        foreach ($request->input('items', []) as $rawItem) {
            /** @var Role $target */
            $target = Role::query()->findOrFail($rawItem['id']);
            RoleAssignment::authorizeManageRole($request->user(), $target);
            RoleAssignment::authorizeRolePermissions(
                $request->user(),
                $target->permissions()->pluck('permissions.id')->all(),
                (int) ($rawItem['perimeter_id'] ?? $target->perimeter_id)
            );
        }

        $roleModel = new Role;
        $fillable = $roleModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var Role $role */
            $role = Role::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id)
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $role->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
