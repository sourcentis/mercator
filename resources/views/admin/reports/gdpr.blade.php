@extends('layouts.admin')

@section('title')
    {{ trans('cruds.menu.gdpr.title') }}
@endsection

@section('content')
<div class="graph-card-sticky">
    <div class="card mb-3">
        <div class="card-header">
            {{ trans('cruds.menu.gdpr.title') }}
        </div>
        <form action="/admin/report/gdpr">
        <div class="card-body">
            @if(session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <div class="col-sm-6">
                <table class="table table-bordered table-striped"
                       style="max-width: 600px;">
                    <tr>
                        <td style="min-width: 280px">
                            {{ trans('cruds.macroProcessus.title') }} :
                            <select name="macroprocess"
                                    data-autosubmit data-autosubmit-clear="process"
                                    class="form-control select2">
                                <option value="">-- All --</option>
                                @foreach ($all_macroprocess as $macroprocess)
                                    <option value="{{$macroprocess->id}}" {{ Session::get('macroprocess')==$macroprocess->id ? "selected" : "" }}>{{ $macroprocess->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td style="width: 300px">
                            {{ trans('cruds.process.title') }} :
                            <select name="process" data-autosubmit
                                    class="form-control select2">
                                <option value="">-- All --</option>
                                @if ($all_process!=null)
                                    @foreach ($all_process as $process)
                                        <option value="{{$process->id}}" {{ Session::get('process')==$process->id ? "selected" : "" }}>{{ $process->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
            <div id="graph-container">
                <div class="graphviz" id="graph">
                    @include('admin.reports._graph_too_large')
                </div>
                <div class="graph-resize-handle"></div>
            </div>
            <div class="row p-1">
                <div class="col-4">

                    @php($engines=["dot", "fdp",  "osage", "circo" ])
                    @php($engine = request()->get('engine', 'dot'))

                    <label class="inline-flex items-center ps-1 pe-1">
                        <a href="#" id="downloadSvg"><i class="bi bi-download"></i></a>
                    </label>

                    <label class="inline-flex items-center">
                        Rendu :
                    </label>
                    @foreach($engines as $value)
                        <label class="inline-flex items-center ps-1">
                            <input
                                    type="radio"
                                    name="engine"
                                    value="{{ $value }}"
                                    @checked($engine === $value)
                            >
                            <span>{{ $value }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </form>
</div>
</div>
{{-- Graphe trop grand : les objets ne sont pas listés non plus --}}
@if(empty($graphTooLarge))
<div class="report-scroll-area">
    @canAccess(App\Models\DataProcessing::class)
        <div class="card">
            <div class="card-header">
                {{ trans('cruds.dataProcessing.title') }}
            </div>
            <div class="card-body">
                <p>{{ trans('cruds.dataProcessing.description') }}</p>
                @foreach($dataProcessings as $dataProcessing)
                    <div class="row">
                        <div class="col">
                            @include('admin.dataProcessing._details', [
                                'dataProcessing' => $dataProcessing,
                                'withLink' => true,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endcan
 </div>
@endif
@endsection

@section('scripts')
    @vite(['resources/js/graphviz.js'])
    <script @nonce>
        let dotSrc = @json($dotSrc);

        document.addEventListener('graphvizReady', () => {
            window.initGraphvizReport({ dotSrc, engine: @json($engine), images: @json($imageManifest) });
        });
    </script>
    @parent
@endsection