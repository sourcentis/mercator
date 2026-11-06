<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyDomainRequest;
use App\Http\Requests\MassStoreDomainRequest;
use App\Http\Requests\MassUpdateDomainRequest;
use App\Http\Requests\StoreDomainRequest;
use App\Http\Requests\UpdateDomainRequest;
use App\Models\Domain;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class DomainController extends APIController
{
    protected string $modelClass = Domain::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('domain_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreDomainRequest $request)
    {
        abort_if(Gate::denies('domain_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var Domain $domain */
        $domain = Domain::create($request->all());

        if ($request->has('forestAds') && $request->input('forestAds') !== null) {
            $domain->forestAds()->sync($request->input('forestAds', []));
        }

        return response()->json($domain, Response::HTTP_CREATED);
    }

    public function show(Domain $domain)
    {
        abort_if(Gate::denies('show-object', $domain), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new JsonResource($domain);
    }

    public function update(UpdateDomainRequest $request, Domain $domain)
    {
        abort_if(Gate::denies('edit-object', $domain), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $domain->update($request->all());

        if ($request->has('forestAds') && $request->input('forestAds') !== null) {
            $domain->forestAds()->sync($request->input('forestAds', []));
        }

        return response()->json();
    }

    public function destroy(Domain $domain)
    {
        abort_if(Gate::denies('domain_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $domain->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyDomainRequest $request)
    {
        abort_if(Gate::denies('domain_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Domain::whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreDomainRequest $request)
    {
        // L’authorize() du FormRequest gère déjà domain_create
        $createdIds = [];

        $model = new Domain;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $item) {
            $forestAds = $item['forestAds'] ?? null;

            // Ne garde que les colonnes du modèle, sans les relations
            $attributes = collect($item)
                ->except(['forestAds'])
                ->only($fillable)
                ->toArray();

            /** @var Domain $domain */
            $domain = Domain::query()->create($attributes);

            if (array_key_exists('forestAds', $item) && $forestAds !== null) {
                $domain->forestAds()->sync($forestAds);
            }

            $createdIds[] = $domain->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateDomainRequest $request)
    {
        // L’authorize() du FormRequest gère déjà domain_edit
        $model = new Domain;
        $fillable = $model->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $forestAds = $rawItem['forestAds'] ?? null;

            /** @var Domain $domain */
            $domain = Domain::query()->findOrFail($id);

            // Ne garde que les colonnes du modèle, sans l'id ni les relations
            $attributes = collect($rawItem)
                ->except(['id', 'forestAds'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $domain->update($attributes);
            }

            if (array_key_exists('forestAds', $rawItem) && $forestAds !== null) {
                $domain->forestAds()->sync($forestAds);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
