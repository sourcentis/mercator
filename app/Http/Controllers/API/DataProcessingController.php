<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\MassDestroyDataProcessingRequest;
use App\Http\Requests\StoreDataProcessingRequest;
use App\Http\Requests\UpdateDataProcessingRequest;
use App\Models\DataProcessing;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class DataProcessingController extends APIController
{
    protected string $modelClass = DataProcessing::class;

    public function index(Request $request)
    {
        abort_if(Gate::denies('data_processing_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->indexResource($request);
    }

    public function store(StoreDataProcessingRequest $request)
    {
        abort_if(Gate::denies('data_processing_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /** @var DataProcessing $dataProcessing */
        $dataProcessing = DataProcessing::query()->create($request->all());

        $dataProcessing->processes()->sync($request->input('processes', []));
        $dataProcessing->informations()->sync($request->input('informations', []));
        $dataProcessing->applications()->sync($request->input('applications', []));

        return response()->json($dataProcessing, Response::HTTP_CREATED);
    }

    public function show(DataProcessing $dataProcessing)
    {
        abort_if(Gate::denies('show-object', $dataProcessing), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dataProcessing['processes'] = $dataProcessing->processes()->pluck('id');
        $dataProcessing['informations'] = $dataProcessing->informations()->pluck('id');
        $dataProcessing['applications'] = $dataProcessing->applications()->pluck('id');

        return new JsonResource($dataProcessing);
    }

    public function update(UpdateDataProcessingRequest $request, DataProcessing $dataProcessing)
    {
        abort_if(Gate::denies('edit-object', $dataProcessing), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dataProcessing->update($request->all());
        if ($request->has('processes')) {
            $dataProcessing->processes()->sync($request->input('processes', []));
        }
        if ($request->has('informations')) {
            $dataProcessing->informations()->sync($request->input('informations', []));
        }
        if ($request->has('applications')) {
            $dataProcessing->applications()->sync($request->input('applications', []));
        }

        return response()->json();
    }

    public function destroy(DataProcessing $dataProcessing)
    {
        abort_if(Gate::denies('data_processing_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dataProcessing->delete();

        return response()->json();
    }

    public function massDestroy(MassDestroyDataProcessingRequest $request)
    {
        abort_if(Gate::denies('data_processing_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DataProcessing::query()->whereIn('id', $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
