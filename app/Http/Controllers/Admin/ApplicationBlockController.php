<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyApplicationBlockRequest;
use App\Http\Requests\StoreApplicationBlockRequest;
use App\Http\Requests\UpdateApplicationBlockRequest;
use App\Models\Application;
use App\Models\ApplicationBlock;
use App\Models\Cartographer;
use Gate;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApplicationBlockController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allowedIds = Gate::allows('application_block_access') ? null : Cartographer::allowedIdsFor($user, ApplicationBlock::class);
        if ($allowedIds !== null && empty($allowedIds)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $applicationBlocks = ApplicationBlock::with('applications')
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (ApplicationBlock::$searchable as $field) {
                        $q->orWhereRaw('LOWER('.$field.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                    }
                });
            })
            ->orderBy('name')

            ->when($allowedIds !== null, fn ($q) => $q->whereIn('id', $allowedIds))->paginate($this->resolvePerPage());

        return view('admin.applicationBlocks.index', compact('applicationBlocks'));
    }

    public function create()
    {
        abort_if(Gate::denies('application_block_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applications = Application::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->pluck('name', 'id');
        $type_list = ApplicationBlock::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();

        return view('admin.applicationBlocks.create', compact('applications', 'type_list', 'attributes_list'));
    }

    public function store(StoreApplicationBlockRequest $request)
    {
        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $applicationBlock = ApplicationBlock::create($request->all());

        Application::whereIn('id', $request->input('linkToApplications', []))
            ->update(['application_block_id' => $applicationBlock->id, 'updated_by' => Auth::id()]);

        return redirect()->route('admin.application-blocks.index');
    }

    public function edit(ApplicationBlock $applicationBlock)
    {
        abort_if(Gate::denies('edit-object', $applicationBlock), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applications = Application::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->pluck('name', 'id');
        $type_list = ApplicationBlock::query()->select('type')->where('type', '<>', null)->distinct()->orderBy('type')->pluck('type');
        $attributes_list = $this->getAttributes();

        return view('admin.applicationBlocks.edit', compact('applicationBlock', 'applications', 'type_list', 'attributes_list'));
    }

    public function update(UpdateApplicationBlockRequest $request, ApplicationBlock $applicationBlock)
    {
        abort_if(Gate::denies('edit-object', $applicationBlock), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request['attributes'] = implode(' ', $request->get('attributes') !== null ? $request->get('attributes') : []);

        $applicationBlock->update($request->all());

        Application::where('application_block_id', $applicationBlock->id)
            ->update(['application_block_id' => null, 'updated_by' => Auth::id()]);

        Application::whereIn('id', $request->input('linkToApplications', []))
            ->update(['application_block_id' => $applicationBlock->id, 'updated_by' => Auth::id()]);

        return redirect()->route('admin.application-blocks.index');
    }

    public function show(ApplicationBlock $applicationBlock)
    {
        abort_if(Gate::denies('show-object', $applicationBlock), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationBlock->load('applications');

        return view('admin.applicationBlocks.show', compact('applicationBlock'));
    }

    public function destroy(ApplicationBlock $applicationBlock)
    {
        abort_if(Gate::denies('application_block_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationBlock->delete();

        return redirect()->route('admin.application-blocks.index');
    }

    public function massDestroy(MassDestroyApplicationBlockRequest $request)
    {
        ApplicationBlock::whereIn('id', request('ids'))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function getAttributes()
    {
        $attributes_list = ApplicationBlock::query()
            ->select('attributes')
            ->where('attributes', '<>', null)
            ->pluck('attributes');
        $res = [];
        foreach ($attributes_list as $i) {
            foreach (explode(' ', $i) as $j) {
                if (strlen(trim($j)) > 0) {
                    $res[] = trim($j);
                }
            }
        }
        sort($res);

        return array_unique($res);
    }
}
