<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyCertificateRequest;
use App\Http\Requests\MassStoreCertificateRequest;
use App\Http\Requests\MassUpdateCertificateRequest;
use App\Http\Requests\StoreCertificateRequest;
use App\Http\Requests\UpdateCertificateRequest;
use App\Models\Certificate;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class CertificateController extends APIController
{
    protected string $modelClass = Certificate::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('certificate_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreCertificateRequest $request)
    {
        abort_if(Gate::denies('certificate_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $certificate = Certificate::create($request->all());
        $certificate->logicalServers()->sync($request->input('logical_servers', []));
        $certificate->applications()->sync($request->input('applications', []));

        return response()->json($certificate, 201);
    }

    public function show(Certificate $certificate)
    {
        abort_if(Gate::denies('show-object', $certificate), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $certificate['logical_servers'] = $certificate->logicalServers()->pluck('id');
        $certificate['applications'] = $certificate->applications()->pluck('id');

        return new JsonResource($certificate);
    }

    public function update(UpdateCertificateRequest $request, Certificate $certificate)
    {
        abort_if(Gate::denies('edit-object', $certificate), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $certificate->update($request->all());

        if ($request->has('logical_servers')) {
            $certificate->logicalServers()->sync($request->input('logical_servers', []));
        }
        if ($request->has('applications')) {
            $certificate->applications()->sync($request->input('applications', []));
        }

        return response()->json();
    }

    public function destroy(Certificate $certificate)
    {
        abort_if(Gate::denies('certificate_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $certificate->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyCertificateRequest $request)
    {
        abort_if(Gate::denies('certificate_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Certificate::query()->whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function massStore(MassStoreCertificateRequest $request)
    {
        // L’authorize() du FormRequest protège déjà l’accès

        $createdIds = [];
        $certificateModel = new Certificate;
        $fillable = $certificateModel->getFillable();

        foreach ($request->input('items', []) as $item) {
            $logicalServers = $item['logical_servers'] ?? null;
            $applications = $item['applications'] ?? null;

            // On garde uniquement les colonnes du modèle, sans les relations
            $attributes = collect($item)
                ->except(['logical_servers', 'applications'])
                ->only($fillable)
                ->toArray();

            /** @var Certificate $certificate */
            $certificate = Certificate::query()->create($attributes);

            // Relations uniquement si la clé était présente dans le JSON d’origine
            if (array_key_exists('logical_servers', $item)) {
                $certificate->logicalServers()->sync($logicalServers ?? []);
            }

            if (array_key_exists('applications', $item)) {
                $certificate->applications()->sync($applications ?? []);
            }

            $createdIds[] = $certificate->id;
        }

        return response()->json([
            'status' => 'ok',
            'count' => count($createdIds),
            'ids' => $createdIds,
        ], Response::HTTP_CREATED);
    }

    public function massUpdate(MassUpdateCertificateRequest $request)
    {
        // L’authorize() du FormRequest protège déjà l’accès
        $certificateModel = new Certificate;
        $fillable = $certificateModel->getFillable();

        foreach ($request->input('items', []) as $rawItem) {
            $id = $rawItem['id'];
            $logicalServers = $rawItem['logical_servers'] ?? null;
            $applications = $rawItem['applications'] ?? null;

            /** @var Certificate $certificate */
            $certificate = Certificate::query()->findOrFail($id);

            // On ne garde que les colonnes du modèle, sans les relations ni l'id
            $attributes = collect($rawItem)
                ->except(['id', 'logical_servers', 'applications'])
                ->only($fillable)
                ->toArray();

            if (! empty($attributes)) {
                $certificate->update($attributes);
            }

            // Si la clé est présente dans l’item, on sync (même [] -> on vide)
            if (array_key_exists('logical_servers', $rawItem)) {
                $certificate->logicalServers()->sync($logicalServers ?? []);
            }

            if (array_key_exists('applications', $rawItem)) {
                $certificate->applications()->sync($applications ?? []);
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
