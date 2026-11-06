<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroySiteRequest;
use App\Http\Requests\MassStoreSiteRequest;
use App\Http\Requests\MassUpdateSiteRequest;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Models\Site;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class SiteController extends APIController
{
    protected string $modelClass = Site::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('site_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreSiteRequest $request)
    {
        abort_if(Gate::denies('site_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Site $site */
        $site = Site::query()->create($request->all());

        return response()->json($site, Response::HTTP_CREATED);
    }

    public function show(Site $site)
    {
        abort_if(Gate::denies('show-object', $site), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($site);
    }

    public function update(UpdateSiteRequest $request, Site $site)
    {
        abort_if(Gate::denies('edit-object', $site), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $site->update($request->all());

        return response()->json();
    }

    public function destroy(Site $site)
    {
        abort_if(Gate::denies('site_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $site->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroySiteRequest $request)
    {
        abort_if(Gate::denies('site_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Site::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreSiteRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `site_create`

        $createdIds = [];
        $siteModel = new Site;
        $fillable = $siteModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            // Colonnes du modèle uniquement
            $attributes = collect($item)
                ->only($fillable)
                ->toArray();

            /** @var Site $site */
            $site = Site::query()->create($attributes);

            $createdIds[] = $site->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateSiteRequest $request)
    {
        // L’authorize() du FormRequest gère déjà la permission `site_edit`
        $siteModel = new Site;
        $fillable = $siteModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];

            /** @var Site $site */
            $site = Site::query()->findOrFail($id);

            // Colonnes du modèle uniquement (sans id)
            $attributes = collect($rawItem)
                ->except(['id'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $site->update($attributes);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
