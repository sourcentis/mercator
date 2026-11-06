<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyApplicationBlockRequest;
use App\Http\Requests\MassStoreApplicationBlockRequest;
use App\Http\Requests\MassUpdateApplicationBlockRequest;
use App\Http\Requests\StoreApplicationBlockRequest;
use App\Http\Requests\UpdateApplicationBlockRequest;
use App\Models\Application;
use App\Models\ApplicationBlock;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApplicationBlockController extends APIController
{
    protected string $modelClass = ApplicationBlock::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('application_block_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreApplicationBlockRequest $request)
    {
        abort_if(Gate::denies('application_block_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var ApplicationBlock $applicationBlock */
        $applicationBlock = ApplicationBlock::create($request->all());

        // Mise à jour des applications liées
        $applications = $request->input('applications', []);
        if (! empty($applications)) {
            Application::whereIn('id', $applications)
                ->update(['application_block_id' => $applicationBlock->id, 'updated_by' => Auth::id()]);
        }

        return response()->json($applicationBlock, Response::HTTP_CREATED);
    }

    public function show(ApplicationBlock $applicationBlock)
    {
        abort_if(Gate::denies('show-object', $applicationBlock), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($applicationBlock);
    }

    public function update(UpdateApplicationBlockRequest $request, ApplicationBlock $applicationBlock)
    {
        abort_if(Gate::denies('edit-object', $applicationBlock), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationBlock->update($request->all());

        if ($request->has('applications')) {
            $applications = $request->input('applications', []);

            // Même logique que le contrôleur existant : ne fait qu’un update sur les IDs fournis
            if (! empty($applications)) {
                Application::whereIn('id', $applications)
                    ->update(['application_block_id' => $applicationBlock->id, 'updated_by' => Auth::id()]);
            }
        }

        return response()->json();
    }

    public function destroy(ApplicationBlock $applicationBlock)
    {
        abort_if(Gate::denies('application_block_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationBlock->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyApplicationBlockRequest $request)
    {
        abort_if(Gate::denies('application_block_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        ApplicationBlock::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreApplicationBlockRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('application_block_create')
        $createdIds = [];
        $model = new ApplicationBlock;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $item) {
            $applications = $item['applications'] ?? null;

            // Ne garde que les colonnes du modèle, sans les relations
            $attributes = collect($item)
                ->except(['applications'])
                ->only($fillable)
                ->toArray();

            /** @var ApplicationBlock $applicationBlock */
            $applicationBlock = ApplicationBlock::query()->create($attributes);

            if (array_key_exists('applications', $item) && ! empty($applications)) {
                Application::whereIn('id', $applications)
                    ->update(['application_block_id' => $applicationBlock->id, 'updated_by' => Auth::id()]);
            }

            $createdIds[] = $applicationBlock->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateApplicationBlockRequest $request)
    {
        // L’authorize() du FormRequest gère déjà le Gate::denies('application_block_edit')
        $model = new ApplicationBlock;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $applications = $rawItem['applications'] ?? null;

            /** @var ApplicationBlock $applicationBlock */
            $applicationBlock = ApplicationBlock::query()->findOrFail($id);

            // Ne garde que les colonnes du modèle, sans l'id ni les relations
            $attributes = collect($rawItem)
                ->except(['id', 'applications'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $applicationBlock->update($attributes);
            }

            // Si la clé est présente dans l’item, applique la même logique que update()
            if (array_key_exists('applications', $rawItem) && ! empty($applications)) {
                Application::whereIn('id', $applications)
                    ->update(['application_block_id' => $applicationBlock->id, 'updated_by' => Auth::id()]);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
