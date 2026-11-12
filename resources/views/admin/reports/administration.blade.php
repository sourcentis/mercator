@extends('layouts.admin')

@section('title')
    {{ trans('cruds.menu.administration.title') }}
@endsection

@section('content')
<div class="graph-card-sticky">
    <div class="card mb-3">
        <div class="card-header">
            {{ trans('cruds.menu.administration.title') }}
        </div>
        <form action="/admin/report/administration">

            <div class="card-body">
                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="col-sm-6" style="max-width: 800px;">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <td>
                                <label for="zones">{{ trans('cruds.zoneAdmin.title') }}</label>
                                <select name="zones[]" id="zones" class="form-control select2" multiple>
                                    @foreach($all_zones as $id => $name)
                                        <option value="{{ $id }}" @selected(in_array($id, $selectedZones, true))>{{ $name }}</option>
                                    @endforeach
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
        </form>
    </div>
</div>

{{-- Graphe trop grand : les objets ne sont pas listés non plus --}}
@if(empty($graphTooLarge))
<div class="report-scroll-area">
    @canAccess(App\Models\ZoneAdmin::class)
        @if ($zones->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans('cruds.zoneAdmin.title') }}
                </div>

                <div class="card-body">
                    <p>{{ trans('cruds.zoneAdmin.title') }}</p>
                        @foreach($zones as $zoneAdmin)
                            <div class="row">
                                <div class="col">
                                    @include('admin.zoneAdmins._details', [
                                        'zoneAdmin' => $zoneAdmin,
                                        'withLink' => true,
                                    ])
                                </div>
                            </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Annuaire::class)
        @if ($annuaires->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans('cruds.annuaire.title') }}
                </div>

                <div class="card-body">
                    <p>{{ trans('cruds.annuaire.description') }}</p>
                        @foreach($annuaires as $annuaire)
                            <div class="row">
                                <div class="col">
                                    @include('admin.annuaires._details', [
                                        'annuaire' => $annuaire,
                                        'withLink' => true,
                                    ])
                                </div>
                            </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\ForestAd::class)
        @if ($forests->count()>0)
            <div class="card">
                <div class="card-header">
                    {{ trans('cruds.forestAd.title') }}
                </div>

                <div class="card-body">
                    <p>{{ trans('cruds.forestAd.description') }}</p>
                        @foreach($forests as $forestAd)
                            <div class="row">
                                <div class="col">
                                    @include('admin.forestAds._details', [
                                        'forestAd' => $forestAd,
                                        'withLink' => true,
                                    ])
                                </div>
                            </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Domain::class)
        @if ($domains->count()>0)
            <div class="card">
                <div class="card-header">
                    {{ trans('cruds.domain.title') }}
                </div>
                <div class="card-body">
                    <p>{{ trans('cruds.domain.description') }}</p>
                        @foreach($domains as $domain)
                            <div class="row">
                                <div class="col">
                                    @include('admin.domains._details', [
                                        'domain' => $domain,
                                        'withLink' => true,
                                    ])
                                </div>
                            </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan
    @canAccess(App\Models\AdminUser::class)
        @if ($adminUsers->count()>0)
            <div class="card">
                <div class="card-header">
                    {{ trans('cruds.adminUser.title') }}
                </div>
                <div class="card-body">
                    <p>{{ trans('cruds.adminUser.description') }}</p>
                        @foreach($adminUsers as $adminUser)
                            <div class="row">
                                <div class="col">
                                    @include('admin.adminUser._details', [
                                        'adminUser' => $adminUser,
                                        'withLink' => true,
                                    ])
                                </div>
                            </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan
</div>
@endif
@endsection

@section('scripts')
@vite(['resources/js/graphviz.js'])

<script @nonce>
let dotSrc = @json($dotSrc);

// Select2 déclenche un événement jQuery : on l'écoute via jQuery, une fois
// les modules Vite (jQuery) chargés
document.addEventListener('DOMContentLoaded', () => {
    $('#zones').on('change', function () {
        this.form.submit();
    });
});

document.addEventListener('graphvizReady', () => {
    window.initGraphvizReport({ dotSrc, engine: @json($engine), images: @json($imageManifest) });
});
</script>
@parent
@endsection