@extends('layouts.admin')

@section('title')
    {{ trans("cruds.menu.physical_infrastructure.title") }}
@endsection

@section('content')
<div class="graph-card-sticky">
    <div class="card mb-3">
        <div class="card-header">
            {{ trans("cruds.menu.physical_infrastructure.title") }}
        </div>
        <form action="/admin/report/physical_infrastructure">

            <div class="card-body">
                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="col-sm-4">
                    <table class="table table-bordered table-striped"
                           style="max-width: 600px; width: 100%;">
                        <tr>
                            <td style="width: 50%;">
                                {{ trans("cruds.site.title_singular") }} :
                                <select name="site" id="site"
                                        data-autosubmit data-autosubmit-clear="building"
                                        class="form-control select2">
                                    <option value="">-- All sites --</option>
                                    @foreach($all_sites as $id => $name)
                                        <option value="{{$id}}" {{ Session::get('site')==$id ? "selected" : "" }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="width: 50%;">
                                {{ trans("cruds.building.title_singular") }} :
                                <select name="building" id="building" data-autosubmit
                                        class="form-control select2">
                                    <option value="">-- All buildings --</option>
                                    @if ($all_buildings!=null)
                                        @foreach($all_buildings as $id => $name)
                                            <option value="{{$id}}" {{ Session::get('building')==$id ? "selected" : "" }}>{{ $name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="graph-container">
                    <div id="graph" class="graphviz">
                        @include('admin.reports._graph_too_large')
                    </div>
                    <div class="graph-resize-handle"></div>
                </div>

                <div class="row p-1">
                    <div class="col-4">
                        @php
                            $engines = ["dot", "fdp", "osage", "circo"];
                            $engine = request()->get('engine', 'dot');
                        @endphp

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
    @canAccess(App\Models\Site::class)
        @if ($sites->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.site.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.site.description") }}</p>
                    @foreach($sites as $site)
                        <div class="row">
                            <div class="col">
                                @include('admin.sites._details', [
                                    'site' => $site,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Building::class)
        @if ($buildings->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.building.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.building.description") }}</p>
                    @foreach($buildings as $building)
                        <div class="row">
                            <div class="col">
                                @include('admin.buildings._details', [
                                    'building' => $building,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Bay::class)
        @if ($bays->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.bay.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.bay.description") }}</p>
                    @foreach($bays as $bay)
                        <div class="row">
                            <div class="col">
                                @include('admin.bays._details', [
                                    'bay' => $bay,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\PhysicalServer::class)
        @if ($physicalServers->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.physicalServer.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.physicalServer.description") }}</p>
                    @foreach($physicalServers as $physicalServer)
                        <div class="row">
                            <div class="col">
                                @include('admin.physicalServers._details', [
                                    'physicalServer' => $physicalServer,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Workstation::class)
        @if ($workstations->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.workstation.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.workstation.description") }}</p>
                    @foreach($workstations as $workstation)
                        <div class="row">
                            <div class="col">
                                @include('admin.workstations._details', [
                                    'workstation' => $workstation,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\StorageDevice::class)
        @if ($storageDevices->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.storageDevice.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.storageDevice.description") }}</p>
                    @foreach($storageDevices as $storageDevice)
                        <div class="row">
                            <div class="col">
                                @include('admin.storageDevices._details', [
                                    'storageDevice' => $storageDevice,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Peripheral::class)
        @if ($peripherals->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.peripheral.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.peripheral.description") }}</p>
                    @foreach($peripherals as $peripheral)
                        <div class="row">
                            <div class="col">
                                @include('admin.peripherals._details', [
                                    'peripheral' => $peripheral,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Phone::class)
        @if ($phones->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.phone.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.phone.description") }}</p>
                    @foreach($phones as $phone)
                        <div class="row">
                            <div class="col">
                                @include('admin.phones._details', [
                                    'phone' => $phone,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\PhysicalSwitch::class)
        @if ($physicalSwitches->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.physicalSwitch.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.physicalSwitch.description") }}</p>
                    @foreach($physicalSwitches as $physicalSwitch)
                        <div class="row">
                            <div class="col">
                                @include('admin.physicalSwitches._details', [
                                    'physicalSwitch' => $physicalSwitch,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\PhysicalRouter::class)
        @if ($physicalRouters->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.physicalRouter.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.physicalRouter.description") }}</p>
                    @foreach($physicalRouters as $physicalRouter)
                        <div class="row">
                            <div class="col">
                                @include('admin.physicalRouters._details', [
                                    'physicalRouter' => $physicalRouter,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\WifiTerminal::class)
        @if ($wifiTerminals->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.wifiTerminal.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.wifiTerminal.description") }}</p>
                    @foreach($wifiTerminals as $wifiTerminal)
                        <div class="row">
                            <div class="col">
                                @include('admin.wifiTerminals._details', [
                                    'wifiTerminal' => $wifiTerminal,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\PhysicalSecurityDevice::class)
        @if ($physicalSecurityDevices->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.physicalSecurityDevice.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.physicalSecurityDevice.description") }}</p>
                    @foreach($physicalSecurityDevices as $physicalSecurityDevice)
                        <div class="row">
                            <div class="col">
                                @include('admin.physicalSecurityDevices._details', [
                                    'physicalSecurityDevice' => $physicalSecurityDevice,
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

document.addEventListener('graphvizReady', () => {
    window.initGraphvizReport({ dotSrc, engine: @json($engine), images: @json($imageManifest) });
});
</script>
@parent
@endsection
