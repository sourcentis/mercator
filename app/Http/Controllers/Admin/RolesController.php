<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyRoleRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Cartographer;
use App\Models\Perimeter;
use App\Models\Permission;
use App\Models\Role;
use App\Support\RoleAssignment;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RolesController extends Controller
{
    /**
     * Triage des permissions pour un meilleur affichage dans la vue Blade
     *
     * @param  Collection  $permissions  Tableau des permissions sur lesquelles le triage sera effectué
     * @return array Tableau avec les permissions triées
     */
    public function getSortedPerms(Collection $permissions): array
    {
        $permissions_sorted = [];

        foreach ($permissions as $id => $permission) {
            $explode = explode('_', $permission);
            if (count($explode) >= 2) {
                $sliced = array_slice($explode, 0, -1);
                $name = implode('_', $sliced);
                $action = $explode[count($explode) - 1];
            } else {
                $name = $explode[0];
                $action = $name;
            }
            $actionTab = [$id, $action];
            if (! isset($permissions_sorted[$name])) {
                $permissions_sorted[$name] = ['name' => $name, 'actions' => []];
            }
            $permissions_sorted[$name]['actions'][] = $actionTab;
        }

        return $permissions_sorted;
    }

    public function index()
    {
        abort_if(Gate::denies('role_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = Role::withCount('users')
            ->with(['cartographerEntries.cartographiable', 'perimeter'])
            ->orderBy('id')
            ->get();
        $routes = Cartographer::cartographiableRoutesMap();
        $models = Cartographer::cartographiableModelsList();

        return view('admin.roles.index', compact('roles', 'routes', 'models'));
    }

    public function clone(Request $request)
    {
        abort_if(Gate::denies('role_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $role = Role::find($request['id']);
        abort_if($role === null, Response::HTTP_NOT_FOUND, '404 Not Found');

        $permissions = Permission::all()->sortBy('title')->pluck('title', 'id');
        $permissions_sorted = $this->getSortedPerms($permissions);
        $perimeters = Perimeter::query()->orderBy('id')->get();

        $request->merge($role->only($role->getFillable()));
        $request->merge(['permissions' => $role->permissions()->pluck('id')->toArray()]);
        $request->flash();

        $grantable = RoleAssignment::grantablePermissionIds(auth()->user());

        return view('admin.roles.create', compact('permissions_sorted', 'perimeters', 'grantable'));
    }

    public function create()
    {
        abort_if(Gate::denies('role_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Chargement de toutes les permissions et triage
        $permissions = Permission::all()->sortBy('title')->pluck('title', 'id');
        $permissions_sorted = $this->getSortedPerms($permissions);
        $perimeters = Perimeter::query()->orderBy('id')->get();

        $grantable = RoleAssignment::grantablePermissionIds(auth()->user());

        return view('admin.roles.create', compact('permissions_sorted', 'perimeters', 'grantable'));
    }

    public function store(StoreRoleRequest $request)
    {
        abort_if(Gate::denies('role_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        RoleAssignment::authorizeRolePermissions(
            auth()->user(),
            $request->input('permissions', []),
            (int) $request->input('perimeter_id')
        );

        $role = Role::query()->create($request->all());
        $role->permissions()->sync($request->input('permissions', []));

        Cache::forget('permissions_roles_map');
        Cache::put('roles_last_update', now()->timestamp);

        return redirect()->route('admin.roles.index');
    }

    public function edit(Role $role)
    {
        abort_if(Gate::denies('edit-object', $role), Response::HTTP_FORBIDDEN, '403 Forbidden');
        RoleAssignment::authorizeManageRole(auth()->user(), $role);

        // Chargement de toutes les permissions et triage
        $permissions = Permission::all()->sortBy('title')->pluck('title', 'id');
        $permissions_sorted = $this->getSortedPerms($permissions);
        $perimeters = Perimeter::query()->orderBy('id')->get();

        $role->load(['permissions', 'perimeter']);
        $cartographers = $role->cartographerEntries()->with('cartographiable')->orderBy('cartographiable_type')->get();
        $cartographiableModels = Cartographer::cartographiableModelsList();

        $grantable = RoleAssignment::grantablePermissionIds(auth()->user());

        return view('admin.roles.edit', compact('permissions_sorted', 'role', 'cartographers', 'cartographiableModels', 'perimeters', 'grantable'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        abort_if(Gate::denies('edit-object', $role), Response::HTTP_FORBIDDEN, '403 Forbidden');
        RoleAssignment::authorizeManageRole(auth()->user(), $role);
        RoleAssignment::authorizeRolePermissions(
            auth()->user(),
            $request->input('permissions', []),
            (int) $request->input('perimeter_id')
        );

        $role->update($request->all());
        $role->permissions()->sync($request->input('permissions', []));

        Cache::forget('permissions_roles_map');
        Cache::put('roles_last_update', now()->timestamp);

        return redirect()->route('admin.roles.index');
    }

    public function show(Role $role)
    {
        abort_if(Gate::denies('role_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $permissions = Permission::all()->sortBy('title')->pluck('title', 'id');
        $permissions_sorted = $this->getSortedPerms($permissions);
        $role->load(['permissions', 'perimeter']);
        $cartographers = $role->cartographerEntries()->with('cartographiable')->orderBy('cartographiable_type')->get();
        $cartographiableModels = Cartographer::cartographiableModelsList();

        return view('admin.roles.show', compact('permissions_sorted', 'role', 'cartographers', 'cartographiableModels'));
    }

    public function destroy(Role $role)
    {
        abort_if(Gate::denies('role_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        RoleAssignment::authorizeManageRole(auth()->user(), $role);

        if ($role->users()->count() > 0) {
            return back()->withErrors('This role is assigned to at least one user');
        }

        $role->delete();

        return redirect()->route('admin.roles.index');
    }

    public function massDestroy(MassDestroyRoleRequest $request)
    {
        abort_if(Gate::denies('role_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = Role::query()->whereIn('id', request('ids'))->get();
        $roles->each(fn (Role $role) => RoleAssignment::authorizeManageRole(auth()->user(), $role));
        $roles->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
