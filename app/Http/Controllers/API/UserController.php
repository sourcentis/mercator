<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyUserRequest;
use App\Http\Requests\MassStoreUserRequest;
use App\Http\Requests\MassUpdateUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Support\RoleAssignment;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class UserController extends APIController
{
    protected string $modelClass = User::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreUserRequest $request)
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        RoleAssignment::authorizeRoles($request->user(), null, $request->input('roles', []));

        /** @var User $user */
        $user = User::query()->create($request->all());

        $user->roles()->sync($request->input('roles', []));

        return response()->json($user, Response::HTTP_CREATED);
    }

    public function show(User $user)
    {
        abort_if(Gate::denies('show-object', $user), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user['roles'] = $user->roles()->pluck('id');

        return new JsonResource($user);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        abort_if(Gate::denies('edit-object', $user), Response::HTTP_FORBIDDEN, '403 Forbidden');
        RoleAssignment::authorizeManage($request->user(), $user);
        if ($request->has('roles')) {
            RoleAssignment::authorizeRoles($request->user(), $user, $request->input('roles', []));
        }

        $user->update($request->all());

        if ($request->has('roles')) {
            $user->roles()->sync($request->input('roles', []));
        }

        return response()->json();
    }

    public function destroy(Request $request, User $user)
    {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        RoleAssignment::authorizeManage($request->user(), $user);

        $user->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyUserRequest $request)
    {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        User::whereIn('id', $request->input('ids', []))->get()
            ->each(fn (User $user) => RoleAssignment::authorizeManage($request->user(), $user));

        User::whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreUserRequest $request)
    {

        foreach ($request->input('items', []) as $item) {
            RoleAssignment::authorizeRoles($request->user(), null, $item['roles'] ?? []);
        }

        $createdIds = [];
        $userModel = new User;
        $fillable = $userModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            $roles = $item['roles'] ?? null;

            // Colonnes du modèle uniquement (sans relations)
            $attributes = collect($item)
                ->except(['roles'])
                ->only($fillable)
                ->toArray();

            /** @var User $user */
            $user = User::query()->create($attributes);

            if (array_key_exists('roles', $item)) {
                $user->roles()->sync($roles ?? []);
            }

            $createdIds[] = $user->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateUserRequest $request)
    {
        foreach ($request->input('items', []) as $rawItem) {
            /** @var User $target */
            $target = User::query()->findOrFail($rawItem['id']);
            RoleAssignment::authorizeManage($request->user(), $target);
            if (array_key_exists('roles', $rawItem)) {
                RoleAssignment::authorizeRoles($request->user(), $target, $rawItem['roles'] ?? []);
            }
        }

        $userModel = new User;
        $fillable = $userModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $roles = $rawItem['roles'] ?? null;

            /** @var User $user */
            $user = User::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id ni relations)
            $attributes = collect($rawItem)
                ->except(['id', 'roles'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $user->update($attributes);
            }

            if (array_key_exists('roles', $rawItem)) {
                $user->roles()->sync($roles ?? []);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
