<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyDataProcessingRequest;
use App\Http\Requests\StoreDataProcessingRequest;
use App\Http\Requests\UpdateDataProcessingRequest;
use App\Models\Application;
use App\Models\DataProcessing;
use App\Models\Information;
use App\Models\Process;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class DataProcessingController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('data_processing_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $processingRegister = DataProcessing::query()
            ->with('processes.securityControls', 'informations', 'applications.securityControls')
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (DataProcessing::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')
            ->paginate($this->resolvePerPage());

        return view('admin.dataProcessing.index', compact('processingRegister'));
    }

    public function create()
    {
        abort_if(Gate::denies('data_processing_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $processes = Process::query()->orderBy('name')->pluck('name', 'id');
        $informations = Information::query()->orderBy('name')->pluck('name', 'id');
        $applications = Application::query()->orderBy('name')->pluck('name', 'id');

        // Get Legal Basis
        $legal_basis_list = DataProcessing::query()
            ->select('legal_basis')
            ->whereNotNull('legal_basis')
            ->distinct()
            ->orderBy('legal_basis')
            ->pluck('legal_basis');
        $res = [];
        foreach ($legal_basis_list as $i) {
            foreach (explode(',', $i) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        $legal_basis_list = array_unique($res);

        // Clear documents from session
        session()->put('documents', []);

        return view(
            'admin.dataProcessing.create',
            compact('applications', 'informations', 'processes', 'legal_basis_list')
        );
    }

    public function store(StoreDataProcessingRequest $request)
    {
        $request->merge(['legal_basis' => implode(', ', $request->legal_bases !== null ? $request->legal_bases : [])]);

        // Create DataProcessing
        $dataProcessing = DataProcessing::create($request->all());

        // Links
        $dataProcessing->processes()->sync($request->input('processes', []));
        $dataProcessing->informations()->sync($request->input('informations', []));
        $dataProcessing->applications()->sync($request->input('applications', []));
        $dataProcessing->documents()->sync(session()->get('documents'));

        // Booleans
        $dataProcessing->lawfulness_legitimate_interest = $request->has('lawfulness_legitimate_interest');
        $dataProcessing->lawfulness_public_interest = $request->has('lawfulness_public_interest');
        $dataProcessing->lawfulness_vital_interest = $request->has('lawfulness_vital_interest');
        $dataProcessing->lawfulness_legal_obligation = $request->has('lawfulness_legal_obligation');
        $dataProcessing->lawfulness_contract = $request->has('lawfulness_contract');
        $dataProcessing->lawfulness_consent = $request->has('lawfulness_consent');

        // Save
        $dataProcessing->save();

        // Forget the documents
        session()->forget('documents');

        return redirect()->route('admin.data-processings.index');
    }

    public function edit(DataProcessing $dataProcessing)
    {
        abort_if(Gate::denies('edit-object', $dataProcessing), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $processes = Process::select(['id', 'name'])->orderBy('name')->get();
        $informations = Information::select(['id', 'name'])->orderBy('name')->get();
        $applications = Application::select(['id', 'name'])->orderBy('name')->get();

        // Get Legal Basis
        $legal_basis_list = DataProcessing::select('legal_basis')
            ->whereNotNull('legal_basis')
            ->distinct()
            ->orderBy('legal_basis')
            ->pluck('legal_basis');
        $res = [];
        foreach ($legal_basis_list as $i) {
            foreach (explode(',', $i) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        $legal_basis_list = array_unique($res);

        // Load linked tables
        $dataProcessing->load('applications', 'informations', 'processes', 'documents');

        // Get Documents
        $documents = [];
        foreach ($dataProcessing->documents as $doc) {
            $documents[] = $doc->id;
        }
        session()->put('documents', $documents);

        return view(
            'admin.dataProcessing.edit',
            compact('dataProcessing', 'applications', 'informations', 'processes', 'legal_basis_list')
        );
    }

    public function update(UpdateDataProcessingRequest $request, DataProcessing $dataProcessing)
    {
        abort_if(Gate::denies('edit-object', $dataProcessing), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dataProcessing->legal_basis = implode(', ', $request->legal_bases !== null ? $request->legal_bases : []);

        // Update
        $dataProcessing->update($request->all());

        // Links
        $dataProcessing->processes()->sync($request->input('processes', []));
        $dataProcessing->applications()->sync($request->input('applications', []));
        $dataProcessing->informations()->sync($request->input('informations', []));
        $dataProcessing->documents()->sync(session()->get('documents'));

        // Booleans
        $dataProcessing->lawfulness_legitimate_interest = $request->has('lawfulness_legitimate_interest');
        $dataProcessing->lawfulness_public_interest = $request->has('lawfulness_public_interest');
        $dataProcessing->lawfulness_vital_interest = $request->has('lawfulness_vital_interest');
        $dataProcessing->lawfulness_legal_obligation = $request->has('lawfulness_legal_obligation');
        $dataProcessing->lawfulness_contract = $request->has('lawfulness_contract');
        $dataProcessing->lawfulness_consent = $request->has('lawfulness_consent');

        $dataProcessing->update();

        // Forget Documents
        session()->forget('documents');

        return redirect()->route('admin.data-processings.index');
    }

    public function show(DataProcessing $dataProcessing)
    {
        abort_if(Gate::denies('data_processing_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dataProcessing->load('applications.securityControls', 'informations', 'processes.securityControls');

        return view('admin.dataProcessing.show', compact('dataProcessing'));
    }

    public function destroy(DataProcessing $dataProcessing)
    {
        abort_if(Gate::denies('data_processing_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dataProcessing->delete();

        return redirect()->route('admin.data-processings.index');
    }

    public function massDestroy(MassDestroyDataProcessingRequest $request)
    {
        DataProcessing::whereIn('id', request('ids'))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
