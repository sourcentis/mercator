@extends('layouts.admin')

@section('title')
    {{ trans("cruds.menu.logical_infrastructure.title") }}
@endsection

@section('content')
<div class="graph-card-sticky">
    <div class="card mb-3">
        <div class="card-header">
            {{ trans("cruds.menu.logical_infrastructure.title") }}
        </div>
        <form action="/admin/report/logical_infrastructure">

            <div class="card-body">
                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="col-sm-4">
                    <table class="table table-bordered table-striped" style="width: 600px;">
                        <tr>
                            <td style="width: 300px;">
                                {{ trans("cruds.network.title_singular") }} :
                                <select name="network" id="network"
                                        onchange="this.form.subnetwork.value='';this.form.submit()"
                                        class="form-control select2">
                                    <option value="">-- All networks --</option>
                                    @foreach($all_networks as $id => $name)
                                        <option value="{{$id}}" {{ Session::get('network')==$id ? "selected" : "" }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="width: 300px;">
                                {{ trans("cruds.subnetwork.title_singular") }} :
                                <select name="subnetwork" id="subnetwork" onchange="this.form.submit()"
                                        class="form-control select2">
                                    <option value="">-- All subnetworks --</option>
                                    @if ($all_subnetworks!==null)
                                        @foreach($all_subnetworks as $id => $name)
                                            <option value="{{$id}}" {{ Session::get('subnetwork')==$id ? "selected" : "" }}>{{ $name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </td>
                        </tr>
                    </table>
                    <div class="col-sm-8">
                        <input name="show_ip" id='show_ip' type="checkbox" value="1" class="form-check-input"
                               {{ Session::get('show_ip') ? 'checked' : '' }} onchange="this.form.subnetwork.value='';this.form.submit()">
                        <label for="show_ip">Afficher les adresses IP</label>
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

    @canAccess(App\Models\Network::class)
        @if ($networks->count()>0)
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.network.title") }}
                </div>

                <div class="card-body">
                    <p>{{ trans("cruds.network.description") }}</p>

                    @foreach($networks as $network)
                        <div class="row">
                            <div class="col">
                                @include('admin.networks._details', [
                                    'network' => $network,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Subnetwork::class)
        @if ($subnetworks->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.subnetwork.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.subnetwork.description") }}</p>

                    @foreach($subnetworks as $subnetwork)
                        <div class="row">
                            <div class="col">
                                @include('admin.subnetworks._details', [
                                    'subnetwork' => $subnetwork,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Gateway::class)
        @if ($gateways->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.gateway.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.gateway.description") }}</p>
                    @foreach($gateways as $gateway)
                        <div class="row">
                            <div class="col">
                                @include('admin.gateways._details', [
                                    'gateway' => $gateway,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\ExternalConnectedEntity::class)
        @if ($externalConnectedEntities->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.externalConnectedEntity.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.externalConnectedEntity.description") }}</p>
                    @foreach($externalConnectedEntities as $entity)
                        <div class="row">
                            <div class="col">
                                @include('admin.externalConnectedEntities._details', [
                                    'externalConnectedEntity' => $entity,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Router::class)
        @if ($routers->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.router.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.router.description") }}</p>
                    @foreach($routers as $router)
                        <div class="row">
                            <div class="col">
                                @include('admin.routers._details', [
                                    'router' => $router,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\NetworkSwitch::class)
        @if ($networkSwitches->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.networkSwitch.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.networkSwitch.description") }}</p>
                    @foreach($networkSwitches as $networkSwitch)
                        <div class="row">
                            <div class="col">
                                @include('admin.networkSwitches._details', [
                                    'networkSwitch' => $networkSwitch,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Cluster::class)
        @if ($clusters->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.cluster.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.cluster.description") }}</p>
                    @foreach($clusters as $cluster)
                        <div class="row">
                            <div class="col">
                                @include('admin.clusters._details', [
                                    'cluster' => $cluster,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\LogicalServer::class)
        @if ($logicalServers->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.logicalServer.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.logicalServer.description") }}</p>
                    @foreach($logicalServers as $logicalServer)
                        <div class="row">
                            <div class="col">
                                @include('admin.logicalServers._details', [
                                    'logicalServer' => $logicalServer,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Container::class)
        @if ($containers->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.container.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.container.description") }}</p>
                    @foreach($containers as $container)
                        <div class="row">
                            <div class="col">
                                @include('admin.containers._details', [
                                    'container' => $container,
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

    @canAccess(App\Models\Phone::class)
        @if ($phones->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.phone.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.phones.description") }}</p>
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
                        @if ($physicalSecurityDevice->address_ip!==null)
                        <div class="row">
                            <div class="col">
                                @include('admin.physicalSecurityDevices._details', [
                                    'physicalSecurityDevice' => $physicalSecurityDevice,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\SecurityDevice::class)
        @if ($securityDevices->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.securityDevice.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.securityDevice.description") }}</p>
                    @foreach($securityDevices as $securityDevice)
                        <div class="row">
                            <div class="col">
                                @include('admin.securityDevices._details', [
                                    'securityDevice' => $securityDevice,
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
                            <div class="col-sm-6">
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

    @canAccess(App\Models\DhcpServer::class)
        @if ($dhcpServers->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.dhcpServer.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.dhcpServer.description") }}</p>
                    @foreach($dhcpServers as $dhcpServer)
                        <div class="row">
                            <div class="col">
                                @include('admin.dhcpServers._details', [
                                    'dhcpServer' => $dhcpServer,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Dnsserver::class)
        @if ($dnsservers->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.dnsserver.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.dnsserver.description") }}</p>
                    @foreach($dnsservers as $dnsserver)
                        <div class="row">
                            <div class="col">
                                @include('admin.dnsservers._details', [
                                    'dnsserver' => $dnsserver,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Certificate::class)
        @if ($certificates->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.certificate.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.certificate.description") }}</p>
                    @foreach($certificates as $certificate)
                        <div class="row">
                            <div class="col">
                                @include('admin.certificates._details', [
                                    'certificate' => $certificate,
                                    'withLink' => true,
                                ])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    @canAccess(App\Models\Vlan::class)
        @if ($vlans->count()>0)
            <br>
            <div class="card">
                <div class="card-header">
                    {{ trans("cruds.vlan.title") }}
                </div>
                <div class="card-body">
                    <p>{{ trans("cruds.vlan.description") }}</p>
                    @foreach($vlans as $vlan)
                        <div class="row">
                            <div class="col">
                                @include('admin.vlans._details', [
                                    'vlan' => $vlan,
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
