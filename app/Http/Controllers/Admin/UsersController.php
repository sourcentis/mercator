<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Cartographer;
use App\Models\Role;
use App\Models\User;
use App\Support\RoleAssignment;
use Gate;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class UsersController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::with(['roles', 'cartographerEntries.cartographiable'])->get()->sortBy('id');
        $routes = Cartographer::cartographiableRoutesMap();
        $models = Cartographer::cartographiableModelsList();

        return view('admin.users.index', compact('users', 'routes', 'models'));
    }

    public function create()
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = $this->grantableRoles();

        return view('admin.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        RoleAssignment::authorizeRoles(auth()->user(), null, $request->input('roles', []));

        $user = User::create($request->all());

        $user->roles()->sync($request->input('roles', []));
        Cache::put('roles_last_update', now()->timestamp);

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        abort_if(Gate::denies('edit-object', $user), Response::HTTP_FORBIDDEN, '403 Forbidden');
        RoleAssignment::authorizeManage(auth()->user(), $user);

        $roles = $this->grantableRoles();

        $user->load('roles');

        return view('admin.users.edit', compact('roles', 'user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        abort_if(Gate::denies('edit-object', $user), Response::HTTP_FORBIDDEN, '403 Forbidden');
        RoleAssignment::authorizeManage(auth()->user(), $user);
        RoleAssignment::authorizeRoles(auth()->user(), $user, $request->input('roles', []));

        $data = $request->all();
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);

        $user->roles()->sync($request->input('roles', []));
        Cache::put('roles_last_update', now()->timestamp);

        return redirect()->route('admin.users.index');
    }

    public function show(User $user)
    {
        abort_if(Gate::denies('user_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->load(['roles', 'cartographerEntries.cartographiable']);

        // Entrées via les rôles de l'utilisateur
        $roleIds = $user->roles->pluck('id');
        $roleCartographers = Cartographer::whereIn('role_id', $roleIds)
            ->with(['cartographiable', 'role'])
            ->get();

        $routes = Cartographer::cartographiableRoutesMap();
        $models = Cartographer::cartographiableModelsList();

        return view('admin.users.show', compact('user', 'roleCartographers', 'routes', 'models'));
    }

    public function destroy(User $user)
    {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        RoleAssignment::authorizeManage(auth()->user(), $user);

        $user->delete();

        return redirect()->route('admin.users.index');
    }

    public function massDestroy(MassDestroyUserRequest $request)
    {
        $users = User::whereIn('id', request('ids'))->get();
        $users->each(fn (User $user) => RoleAssignment::authorizeManage(auth()->user(), $user));
        $users->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Rôles proposés dans les formulaires : seulement ceux que l'utilisateur courant peut attribuer.
     */
    private function grantableRoles()
    {
        $grantable = RoleAssignment::grantableRoleIds(auth()->user());

        return Role::query()
            ->when($grantable !== null, fn ($query) => $query->whereKey($grantable))
            ->get()
            ->sortBy('title')
            ->pluck('title', 'id');
    }
}
