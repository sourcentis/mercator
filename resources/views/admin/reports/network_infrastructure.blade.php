@extends('layouts.admin')

@section('title')
    {{ trans("cruds.menu.network_schema.title") }}
@endsection

@section('content')
{{-- Computed once: each _details partial below is included once per row (potentially
     hundreds of times on this page), and hasMultiplePerimeters() re-queries roles/role_user
     on every call, so passing it down avoids an N+1 across the whole report. --}}
@php($hasMultiplePerimeters = auth()->user()->hasMultiplePerimeters())
<div class="graph-card-sticky">
    <div class="card mb-3">
        <div class="card-header">
            {{ trans("cruds.menu.network_schema.title") }}
        </div>
        <form action="/admin/report/network_infrastructure">

            <div class="card-body">
                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="col-sm-6" style="max-width: 800px; width: 100%;">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <td style="width: 300px;">
                                {{ trans("cruds.site.title_singular") }} :
                                <select name="site" id="site" class="form-control select2">
                                    <option></option>
                                    @foreach($all_sites as $id => $name)
                                        <option value="{{$id}}" {{ Session::get('site')==$id ? "selected" : "" }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="width: 500px;">
                                {{ trans("cruds.building.title_singular") }} :
                                <select name="buildings[]" id="buildings" class="form-control select2" multiple>
                                    @if ($all_buildings!=null)
                                        @foreach($all_buildings as $id => $name)
                                            <option value="{{$id}}" {{ (Session::get('buildings')!=null) && in_array($id, Session::get('buildings')) ? "selected" : "" }}>{{ $name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </td>
                        </tr>
                    </table>
                    <div class="col-sm-8">
                        <input name="show_ports" id='show_ports' type="checkbox" value="1" class="form-check-input"
                               {{ Session::get('show_ports') ? 'checked' : '' }} data-autosubmit>
                        <label for="show_ports">Afficher les ports source/destination</label>
                    </div>
                </div>
                <div id="graph-container">
                    <div id="graph" class="graphviz">
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
    @canAccess(App\Models\Site::class)
        @if ($sites->count()>0)
            <div class="card mt-2">
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
                                    'hasMultiplePerimeters' => $hasMultiplePerimeters,
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
                                    'hasMultiplePerimeters' => $hasMultiplePerimeters,
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
            <div class="card mt-2">
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
                                    'hasMultiplePerimeters' => $hasMultiplePerimeters,
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
            <div class="card mt-2">
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
                                    'hasMultiplePerimeters' => $hasMultiplePerimeters,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Workstation::class)
        @if ((auth()->user()->granularity>=2)&&($workstations->count()>0))
            <div class="card mt-2">
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
                                    'hasMultiplePerimeters' => $hasMultiplePerimeters,
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
            <div class="card mt-2">
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
                                    'hasMultiplePerimeters' => $hasMultiplePerimeters,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Peripheral::class)
        @if ((auth()->user()->granularity>=2)&&($peripherals->count()>0))
            <div class="card mt-2">
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
                                    'hasMultiplePerimeters' => $hasMultiplePerimeters,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Phone::class)
        @if ((auth()->user()->granularity>=2)&&($phones->count()>0))
            <div class="card mt-2">
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
                                    'hasMultiplePerimeters' => $hasMultiplePerimeters,
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
            <div class="card mt-2">
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
                                    'hasMultiplePerimeters' => $hasMultiplePerimeters,
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
            <div class="card mt-2">
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
                                    'hasMultiplePerimeters' => $hasMultiplePerimeters,
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
            <div class="card mt-2">
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
                                    'hasMultiplePerimeters' => $hasMultiplePerimeters,
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
            <div class="card mt-2">
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
                                    'hasMultiplePerimeters' => $hasMultiplePerimeters,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\PhysicalLink::class)
        @if ($physicalLinks->count()>0)
            <div class="card mt-2">
                <div class="card-header">
                    {{ trans("cruds.physicalLink.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.physicalLink.description") }}</p>

                    <div class="row">
                        <div class="col-sm-6">

                            <table class="table table-bordered table-striped table-hover"
                                   style="max-width: 800px; width: 100%;">
                                <thead>
                                <th></th>
                                <th width='40%'>
                                    {{ trans('cruds.physicalLink.fields.src') }}
                                </th>
                                <th width='10%'>
                                    {{ trans('cruds.physicalLink.fields.src_port') }}
                                </th>
                                <th width='40%'>
                                    {{ trans('cruds.physicalLink.fields.dest') }}
                                </th>
                                <th width='10%'>
                                    {{ trans('cruds.physicalLink.fields.dest_port') }}
                                </th>
                                </thead>

                                @foreach($physicalLinks as $physicalLink)
                                    <tr data-entry-id="{{ $physicalLink->id }}">
                                        <td>
                                            <a href="/admin/physical-links/{{ $physicalLink->id }}">&#9741;</a>
                                        </td>
                                        <td>
                                            @if ($physicalLink->peripheralSrc!=null)
                                                <a href="{{ route('admin.peripherals.show', $physicalLink->peripheral_src_id) }}">
                                                    {{ $physicalLink->peripheralSrc->name }}
                                                </a>
                                            @elseif ($physicalLink->phoneSrc!=null)
                                                <a href="{{ route('admin.phones.show', $physicalLink->phone_src_id) }}">
                                                    {{ $physicalLink->phoneSrc->name }}
                                                </a>
                                            @elseif ($physicalLink->physicalRouterSrc!=null)
                                                <a href="{{ route('admin.physical-routers.show', $physicalLink->physical_router_src_id) }}">
                                                    {{ $physicalLink->physicalRouterSrc->name }}
                                                </a>
                                            @elseif ($physicalLink->physicalSecurityDeviceSrc!=null)
                                                <a href="{{ route('admin.physical-security-devices.show', $physicalLink->physical_security_device_src_id) }}">
                                                    {{ $physicalLink->physicalSecurityDeviceSrc->name }}
                                                </a>
                                            @elseif ($physicalLink->physicalServerSrc!=null)
                                                <a href="{{ route('admin.physical-servers.show', $physicalLink->physical_server_src_id) }}">
                                                    {{ $physicalLink->physicalServerSrc->name }}
                                                </a>
                                            @elseif ($physicalLink->physicalSwitchSrc!=null)
                                                <a href="{{ route('admin.physical-switches.show', $physicalLink->physical_switch_src_id) }}">
                                                    {{ $physicalLink->physicalSwitchSrc->name }}
                                                </a>
                                            @elseif ($physicalLink->storageDeviceSrc!=null)
                                                <a href="{{ route('admin.storage-devices.show', $physicalLink->storage_device_src_id) }}">
                                                    {{ $physicalLink->storageDeviceSrc->name }}
                                                </a>
                                            @elseif ($physicalLink->wifiTerminalSrc!=null)
                                                <a href="{{ route('admin.wifi-terminals.show', $physicalLink->wifi_terminal_src_id) }}">
                                                    {{ $physicalLink->wifiTerminalSrc->name }}
                                                </a>
                                            @elseif ($physicalLink->workstationSrc!=null)
                                                <a href="{{ route('admin.workstations.show', $physicalLink->workstation_src_id) }}">
                                                    {{ $physicalLink->workstationSrc->name }}
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $physicalLink->src_port }}
                                        </td>
                                        <td>
                                            @if ($physicalLink->peripheralDest!=null)
                                                <a href="{{ route('admin.peripherals.show', $physicalLink->peripheral_dest_id) }}">
                                                    {{ $physicalLink->peripheralDest->name }}
                                                </a>
                                            @elseif ($physicalLink->phoneDest!=null)
                                                <a href="{{ route('admin.phones.show', $physicalLink->phone_dest_id) }}">
                                                    {{ $physicalLink->phoneDest->name }}
                                                </a>
                                            @elseif ($physicalLink->physicalRouterDest!=null)
                                                <a href="{{ route('admin.physical-routers.show', $physicalLink->physical_router_dest_id) }}">
                                                    {{ $physicalLink->physicalRouterDest->name }}
                                                </a>
                                            @elseif ($physicalLink->physicalSecurityDeviceDest!=null)
                                                <a href="{{ route('admin.physical-security-devices.show', $physicalLink->physical_security_device_dest_id) }}">
                                                    {{ $physicalLink->physicalSecurityDeviceDest->name }}
                                                </a>
                                            @elseif ($physicalLink->physicalServerDest!=null)
                                                <a href="{{ route('admin.physical-servers.show', $physicalLink->physical_server_dest_id) }}">
                                                    {{ $physicalLink->physicalServerDest->name }}
                                                </a>
                                            @elseif ($physicalLink->physicalSwitchDest!=null)
                                                <a href="{{ route('admin.physical-switches.show', $physicalLink->physical_switch_dest_id) }}">
                                                    {{ $physicalLink->physicalSwitchDest->name }}
                                                </a>
                                            @elseif ($physicalLink->storageDeviceDest!=null)
                                                <a href="{{ route('admin.storage-devices.show', $physicalLink->storage_device_dest_id) }}">
                                                    {{ $physicalLink->storageDeviceDest->name }}
                                                </a>
                                            @elseif ($physicalLink->wifiTerminalDest!=null)
                                                <a href="{{ route('admin.wifi-terminals.show', $physicalLink->wifi_terminal_dest_id) }}">
                                                    {{ $physicalLink->wifiTerminalDest->name }}
                                                </a>
                                            @elseif ($physicalLink->workstationDest!=null)
                                                <a href="{{ route('admin.workstations.show', $physicalLink->workstation_dest_id) }}">
                                                    {{ $physicalLink->workstationDest->name }}
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $physicalLink->dest_port }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
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

        // ─── Listeners formulaire (DOM uniquement, pas besoin du WASM) ────────
        document.addEventListener("DOMContentLoaded", function () {
            $('#site').on('change', function () {
                const buildings = this.form.querySelector('#buildings');
                if (buildings) {
                    Array.from(buildings.options).forEach((opt) => { opt.selected = false; });
                }
                this.form.submit();
            });

            $('#buildings').on('change', function () {
                this.form.submit();
            });
        });

        // ─── Rendu graphviz (attend que le WASM soit prêt) ───────────────────
        document.addEventListener('graphvizReady', () => {
            window.initGraphvizReport({ dotSrc, engine: @json($engine), images: @json($imageManifest) });
        });
    </script>
@parent
@endsection
