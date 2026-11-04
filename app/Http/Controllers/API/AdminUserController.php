<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyAdminUserRequest;
use App\Http\Requests\MassStoreAdminUserRequest;
use App\Http\Requests\MassUpdateAdminUserRequest;
use App\Http\Requests\StoreAdminUserRequest;
use App\Http\Requests\UpdateAdminUserRequest;
use App\Models\AdminUser;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class AdminUserController extends APIController
{
    protected string $modelClass = AdminUser::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('admin_user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreAdminUserRequest $request)
    {
        abort_if(Gate::denies('admin_user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $adminUser = AdminUser::create($request->all());

        return response()->json($adminUser, Response::HTTP_CREATED);
    }

    public function show(AdminUser $adminUser)
    {
        abort_if(Gate::denies('show-object', $adminUser), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($adminUser);
    }

    public function update(UpdateAdminUserRequest $request, AdminUser $adminUser)
    {
        abort_if(Gate::denies('edit-object', $adminUser), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $adminUser->update($request->all());

        return response()->json();
    }

    public function destroy(AdminUser $adminUser)
    {
        abort_if(Gate::denies('admin_user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $adminUser->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyAdminUserRequest $request)
    {
        abort_if(Gate::denies('admin_user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        AdminUser::whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreAdminUserRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('admin_user_create')
        $createdIds = [];

        $userModel = new AdminUser;
        $fillable = $userModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Ne garde que les colonnes du modèle (ignore les champs inconnus)
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var AdminUser $adminUser */
            $adminUser = AdminUser::query()->create($attributes);

            $createdIds[] = $adminUser->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateAdminUserRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('admin_user_edit')
        $userModel = new AdminUser;
        $fillable = $userModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var AdminUser $adminUser */
            $adminUser = AdminUser::query()->findOrFail($id);

            // Ne garde que les colonnes du modèle, sans l'id
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $adminUser->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
