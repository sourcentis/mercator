@extends('layouts.admin')

@section('title')
    {{ trans('global.dashboard') }}
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        {!! trans("panel.maturity_levels") !!}
    </div>
    <div class="card-body">
        <table>
            <tr>
                <td align="center">
                  <a href="/admin/report/maturity1">
                    <div style="width: 350px; height: 180px;">
                      <canvas id="gauge_chart1_div"></canvas>
                    </div>
                    {!! trans("panel.level_1.title_short") !!}
                  </a>
                </td>
                <td align="center">
                  <a href="/admin/report/maturity2">
                    <div style="width: 350px; height: 180px;">
                      <canvas id="gauge_chart2_div"></canvas>
                    </div>
                    {!! trans("panel.level_2.title_short") !!}
                  </a>
                <td align="center">
                  <a href="/admin/report/maturity3">
                    <div style="width: 350px; height: 180px;">
                      <canvas id="gauge_chart3_div"></canvas>
                    </div>
                    {!! trans("panel.level_3.title_short") !!}
                  </a>
                </td>
            </tr>
        </table>
    </div>
  </div>
  <br>
  <div class="card">
    <div class="card-header">
      {!! trans("panel.repartition") !!}
    </div>
    <div class="card-body">
        <div style="width: 1075px; height: 400px;">
          <canvas id="bar_chart_div"></canvas>
        </div>
    </div>
  </div>
  <br>
  <div class="card">
    <div class="card-header">
      {!! trans("panel.treemap") !!}
    </div>
    <div class="card-body">
        <div style="width: 1050px; height: 500px;">
          <canvas id="treemap_chart_div"></canvas>
        </div>
    </div>

</div>
@endsection

@section('scripts')
@php
use App\Models\Cartographer;
$gi        = 0;
$gdprIdx   = Cartographer::canAccessAny([\App\Models\DataProcessing::class, \App\Models\SecurityControl::class])                                                                                                                                                                                                                                                                                                                                    ? $gi++ : -1;
$ecIdx     = Cartographer::canAccessAny([\App\Models\Entity::class, \App\Models\Relation::class])                                                                                                                                                                                                                                                                                                                                                   ? $gi++ : -1;
$metierIdx = Cartographer::canAccessAny([\App\Models\MacroProcessus::class, \App\Models\Process::class, \App\Models\Activity::class, \App\Models\Operation::class, \App\Models\Task::class, \App\Models\Actor::class, \App\Models\Information::class])                                                                                                                                                                                               ? $gi++ : -1;
$appIdx    = Cartographer::canAccessAny([\App\Models\ApplicationBlock::class, \App\Models\Application::class, \App\Models\ApplicationService::class, \App\Models\ApplicationModule::class, \App\Models\Database::class, \App\Models\ApplicationFlow::class])                                                                                                                                                                                         ? $gi++ : -1;
$adminIdx  = Cartographer::canAccessAny([\App\Models\ZoneAdmin::class, \App\Models\Annuaire::class, \App\Models\ForestAd::class, \App\Models\Domain::class])                                                                                                                                                                                                                                                                                        ? $gi++ : -1;
$infraIdx  = Cartographer::canAccessAny([\App\Models\Network::class, \App\Models\Subnetwork::class, \App\Models\Gateway::class, \App\Models\ExternalConnectedEntity::class, \App\Models\NetworkSwitch::class, \App\Models\Router::class, \App\Models\SecurityDevice::class, \App\Models\Cluster::class, \App\Models\LogicalServer::class, \App\Models\Container::class, \App\Models\Vlan::class, \App\Models\Certificate::class])                   ? $gi++ : -1;
$physIdx   = Cartographer::canAccessAny([\App\Models\Site::class, \App\Models\Building::class, \App\Models\Bay::class, \App\Models\Zone::class, \App\Models\PhysicalServer::class, \App\Models\Workstation::class, \App\Models\Phone::class, \App\Models\Peripheral::class, \App\Models\StorageDevice::class, \App\Models\PhysicalSwitch::class, \App\Models\PhysicalRouter::class, \App\Models\Wan::class, \App\Models\Man::class, \App\Models\Lan::class]) ? $gi++ : -1;
@endphp
<script @nonce>
window.chartData = {
maturity1: {{ $maturity1  }},
maturity2: {{ $maturity2  }},
maturity3: {{ $maturity3  }},
barChart: {
  mode: 'single',
  labels: [
      @if($gdprIdx   >= 0) "{!! trans('cruds.menu.gdpr.title_short') !!}", @endif
      @if($ecIdx     >= 0) "{!! trans('cruds.menu.ecosystem.title_short') !!}", @endif
      @if($metierIdx >= 0) "{!! trans('cruds.menu.metier.title_short') !!}", @endif
      @if($appIdx    >= 0) "{!! trans('cruds.menu.application.title_short') !!}", @endif
      @if($adminIdx  >= 0) "{!! trans('cruds.menu.administration.title_short') !!}", @endif
      @if($infraIdx  >= 0) "{!! trans('cruds.menu.logical_infrastructure.title_short') !!}", @endif
      @if($physIdx   >= 0) "{!! trans('cruds.menu.physical_infrastructure.title_short') !!}", @endif
      ],
  datasets: [
  @canAccess(\App\Models\DataProcessing::class)
  @php $d = array_fill(0, $gi, 0); if ($gdprIdx >= 0) $d[$gdprIdx] = $data_processing; @endphp
  { label: "{!! trans('cruds.dataProcessing.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $data_processing !!}, url: "/admin/data-processings" },
  @endcanAccess
  @canAccess(\App\Models\SecurityControl::class)
  @php $d = array_fill(0, $gi, 0); if ($gdprIdx >= 0) $d[$gdprIdx] = $security_controls; @endphp
  { label: "{!! trans('cruds.securityControl.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $security_controls !!}, url: "/admin/security-controls" },
  @endcanAccess
  @canAccess(\App\Models\Entity::class)
  @php $d = array_fill(0, $gi, 0); if ($ecIdx >= 0) $d[$ecIdx] = $entities; @endphp
  { label: "{!! trans('cruds.entity.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $entities !!}, url: "/admin/entities" },
  @endcanAccess
  @canAccess(\App\Models\Relation::class)
  @php $d = array_fill(0, $gi, 0); if ($ecIdx >= 0) $d[$ecIdx] = $relations; @endphp
  { label: "{!! trans('cruds.relation.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $relations !!}, url: "/admin/relations" },
  @endcanAccess
  @canAccess(\App\Models\MacroProcessus::class)
  @php $d = array_fill(0, $gi, 0); if ($metierIdx >= 0) $d[$metierIdx] = $macroProcessuses; @endphp
  { label: "{!! trans('cruds.macroProcessus.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $macroProcessuses !!}, url: "/admin/macro-processuses" },
  @endcanAccess
  @canAccess(\App\Models\Process::class)
  @php $d = array_fill(0, $gi, 0); if ($metierIdx >= 0) $d[$metierIdx] = $processes; @endphp
  { label: "{!! trans('cruds.process.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $processes !!}, url: "/admin/processes" },
  @endcanAccess
  @canAccess(\App\Models\Activity::class)
  @php $d = array_fill(0, $gi, 0); if ($metierIdx >= 0) $d[$metierIdx] = $activities; @endphp
  { label: "{!! trans('cruds.activity.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $activities !!}, url: "/admin/activities" },
  @endcanAccess
  @canAccess(\App\Models\Operation::class)
  @php $d = array_fill(0, $gi, 0); if ($metierIdx >= 0) $d[$metierIdx] = $operations; @endphp
  { label: "{!! trans('cruds.operation.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $operations !!}, url: "/admin/operations" },
  @endcanAccess
  @canAccess(\App\Models\Task::class)
  @php $d = array_fill(0, $gi, 0); if ($metierIdx >= 0) $d[$metierIdx] = $tasks; @endphp
  { label: "{!! trans('cruds.task.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $tasks !!}, url: "/admin/tasks" },
  @endcanAccess
  @canAccess(\App\Models\Actor::class)
  @php $d = array_fill(0, $gi, 0); if ($metierIdx >= 0) $d[$metierIdx] = $actors; @endphp
  { label: "{!! trans('cruds.actor.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $actors !!}, url: "/admin/actors" },
  @endcanAccess
  @canAccess(\App\Models\Information::class)
  @php $d = array_fill(0, $gi, 0); if ($metierIdx >= 0) $d[$metierIdx] = $informations; @endphp
  { label: "{!! trans('cruds.information.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $informations !!}, url: "/admin/information" },
  @endcanAccess
  @canAccess(\App\Models\ApplicationBlock::class)
  @php $d = array_fill(0, $gi, 0); if ($appIdx >= 0) $d[$appIdx] = $applicationBlocks; @endphp
  { label: "{!! trans('cruds.applicationBlock.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $applicationBlocks !!}, url: "/admin/application-blocks" },
  @endcanAccess
  @canAccess(\App\Models\Application::class)
  @php $d = array_fill(0, $gi, 0); if ($appIdx >= 0) $d[$appIdx] = $applications; @endphp
  { label: "{!! trans('cruds.application.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $applications !!}, url: "/admin/applications" },
  @endcanAccess
  @canAccess(\App\Models\ApplicationService::class)
  @php $d = array_fill(0, $gi, 0); if ($appIdx >= 0) $d[$appIdx] = $applicationServices; @endphp
  { label: "{!! trans('cruds.applicationService.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $applicationServices !!}, url: "/admin/application-services" },
  @endcanAccess
  @canAccess(\App\Models\ApplicationModule::class)
  @php $d = array_fill(0, $gi, 0); if ($appIdx >= 0) $d[$appIdx] = $applicationModules; @endphp
  { label: "{!! trans('cruds.applicationModule.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $applicationModules !!}, url: "/admin/application-modules" },
  @endcanAccess
  @canAccess(\App\Models\Database::class)
  @php $d = array_fill(0, $gi, 0); if ($appIdx >= 0) $d[$appIdx] = $databases; @endphp
  { label: "{!! trans('cruds.database.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $databases !!}, url: "/admin/databases" },
  @endcanAccess
  @canAccess(\App\Models\ApplicationFlow::class)
  @php $d = array_fill(0, $gi, 0); if ($appIdx >= 0) $d[$appIdx] = $flows; @endphp
  { label: "{!! trans('cruds.applicationFlow.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $flows !!}, url: "/admin/application-flows" },
  @endcanAccess
  @canAccess(\App\Models\ZoneAdmin::class)
  @php $d = array_fill(0, $gi, 0); if ($adminIdx >= 0) $d[$adminIdx] = $zones_ad; @endphp
  { label: "{!! trans('cruds.zoneAdmin.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!!$zones_ad!!}, url: "/admin/zone-admins" },
  @endcanAccess
  @canAccess(\App\Models\Annuaire::class)
  @php $d = array_fill(0, $gi, 0); if ($adminIdx >= 0) $d[$adminIdx] = $annuaires; @endphp
  { label: "{!! trans('cruds.annuaire.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!!$annuaires!!}, url: "/admin/annuaires" },
  @endcanAccess
  @canAccess(\App\Models\ForestAd::class)
  @php $d = array_fill(0, $gi, 0); if ($adminIdx >= 0) $d[$adminIdx] = $forests; @endphp
  { label: "{!! trans('cruds.forestAd.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!!$forests!!}, url: "/admin/forest-ads" },
  @endcanAccess
  @canAccess(\App\Models\Domain::class)
  @php $d = array_fill(0, $gi, 0); if ($adminIdx >= 0) $d[$adminIdx] = $domains; @endphp
  { label: "{!! trans('cruds.domain.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!!$domains!!}, url: "/admin/domains" },
  @endcanAccess
  @canAccess(\App\Models\Network::class)
  @php $d = array_fill(0, $gi, 0); if ($infraIdx >= 0) $d[$infraIdx] = $networks; @endphp
  { label: "{!! trans('cruds.network.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $networks !!}, url: "/admin/networks" },
  @endcanAccess
  @canAccess(\App\Models\Subnetwork::class)
  @php $d = array_fill(0, $gi, 0); if ($infraIdx >= 0) $d[$infraIdx] = $subnetworks; @endphp
  { label: "{!! trans('cruds.subnetwork.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $subnetworks !!}, url: "/admin/subnetworks" },
  @endcanAccess
  @canAccess(\App\Models\Gateway::class)
  @php $d = array_fill(0, $gi, 0); if ($infraIdx >= 0) $d[$infraIdx] = $gateways; @endphp
  { label: "{!! trans('cruds.gateway.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $gateways !!}, url: "/admin/gateways" },
  @endcanAccess
  @canAccess(\App\Models\ExternalConnectedEntity::class)
  @php $d = array_fill(0, $gi, 0); if ($infraIdx >= 0) $d[$infraIdx] = $externalConnectedEntities; @endphp
  { label: "{!! trans('cruds.externalConnectedEntity.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $externalConnectedEntities !!}, url: "/admin/external-connected-entities" },
  @endcanAccess
  @canAccess(\App\Models\NetworkSwitch::class)
  @php $d = array_fill(0, $gi, 0); if ($infraIdx >= 0) $d[$infraIdx] = $switches; @endphp
  { label: "{!! trans('cruds.networkSwitch.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $switches !!}, url: "/admin/network-switches" },
  @endcanAccess
  @canAccess(\App\Models\Router::class)
  @php $d = array_fill(0, $gi, 0); if ($infraIdx >= 0) $d[$infraIdx] = $routers; @endphp
  { label: "{!! trans('cruds.router.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $routers !!}, url: "/admin/routers" },
  @endcanAccess
  @canAccess(\App\Models\SecurityDevice::class)
  @php $d = array_fill(0, $gi, 0); if ($infraIdx >= 0) $d[$infraIdx] = $securityDevices; @endphp
  { label: "{!! trans('cruds.securityDevice.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $securityDevices !!}, url: "/admin/security-devices" },
  @endcanAccess
  @canAccess(\App\Models\Cluster::class)
  @php $d = array_fill(0, $gi, 0); if ($infraIdx >= 0) $d[$infraIdx] = $clusters; @endphp
  { label: "{!! trans('cruds.cluster.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $clusters !!}, url: "/admin/clusters" },
  @endcanAccess
  @canAccess(\App\Models\LogicalServer::class)
  @php $d = array_fill(0, $gi, 0); if ($infraIdx >= 0) $d[$infraIdx] = $logicalServers; @endphp
  { label: "{!! trans('cruds.logicalServer.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $logicalServers !!}, url: "/admin/logical-servers" },
  @endcanAccess
  @canAccess(\App\Models\Container::class)
  @php $d = array_fill(0, $gi, 0); if ($infraIdx >= 0) $d[$infraIdx] = $containers; @endphp
  { label: "{!! trans('cruds.container.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $containers !!}, url: "/admin/containers" },
  @endcanAccess
  @canAccess(\App\Models\Certificate::class)
  @php $d = array_fill(0, $gi, 0); if ($infraIdx >= 0) $d[$infraIdx] = $certificates; @endphp
  { label: "{!! trans('cruds.certificate.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $certificates !!}, url: "/admin/certificates" },
  @endcanAccess
  @canAccess(\App\Models\Vlan::class)
  @php $d = array_fill(0, $gi, 0); if ($infraIdx >= 0) $d[$infraIdx] = $vlans; @endphp
  { label: "{!! trans('cruds.vlan.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $vlans !!}, url: "/admin/vlans" },
  @endcanAccess
  @canAccess(\App\Models\Site::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $sites; @endphp
  { label: "{!! trans('cruds.site.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $sites !!}, url: "/admin/sites" },
  @endcanAccess
  @canAccess(\App\Models\Building::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $buildings; @endphp
  { label: "{!! trans('cruds.building.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $buildings !!}, url: "/admin/buildings" },
  @endcanAccess
  @canAccess(\App\Models\Bay::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $bays; @endphp
  { label: "{!! trans('cruds.bay.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $bays !!}, url: "/admin/bays" },
  @endcanAccess
  @canAccess(\App\Models\Zone::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $zones; @endphp
  { label: "{!! trans('cruds.zone.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $zones !!}, url: "/admin/zones" },
  @endcanAccess
  @canAccess(\App\Models\PhysicalServer::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $physicalServers; @endphp
  { label: "{!! trans('cruds.physicalServer.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $physicalServers !!}, url: "/admin/physical-servers" },
  @endcanAccess
  @canAccess(\App\Models\Workstation::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $workstations; @endphp
  { label: "{!! trans('cruds.workstation.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $workstations !!}, url: "/admin/workstations" },
  @endcanAccess
  @canAccess(\App\Models\Peripheral::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $peripherals; @endphp
  { label: "{!! trans('cruds.peripheral.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $peripherals !!}, url: "/admin/peripherals" },
  @endcanAccess
  @canAccess(\App\Models\StorageDevice::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $storageDevices; @endphp
  { label: "{!! trans('cruds.storageDevice.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $storageDevices !!}, url: "/admin/storage-devices" },
  @endcanAccess
  @canAccess(\App\Models\PhysicalSwitch::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $physicalSwitchs; @endphp
  { label: "{!! trans('cruds.physicalSwitch.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $physicalSwitchs !!}, url: "/admin/physical-switches" },
  @endcanAccess
  @canAccess(\App\Models\PhysicalRouter::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $physicalRouters; @endphp
  { label: "{!! trans('cruds.physicalRouter.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $physicalRouters !!}, url: "/admin/physical-routers" },
  @endcanAccess
  @canAccess(\App\Models\Phone::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $phones; @endphp
  { label: "{!! trans('cruds.phone.title') !!}", data: [{{ implode(', ', $d) }}], value: {!! $phones !!}, url: "/admin/phones" },
  @endcanAccess
  @canAccess(\App\Models\WifiTerminal::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $wifiTerminals; @endphp
  { label: "{!! trans('cruds.wifiTerminal.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $wifiTerminals !!}, url: "/admin/wifi-terminals" },
  @endcanAccess
  @canAccess(\App\Models\PhysicalSecurityDevice::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $securityDevices; @endphp
  { label: "{!! trans('cruds.physicalSecurityDevice.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $securityDevices !!}, url: "/admin/physical-security-devices" },
  @endcanAccess
  @canAccess(\App\Models\Wan::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $wans; @endphp
  { label: "{!! trans('cruds.wan.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $wans !!}, url: "/admin/wans" },
  @endcanAccess
  @canAccess(\App\Models\Man::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $mans; @endphp
  { label: "{!! trans('cruds.man.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $mans !!}, url: "/admin/mans" },
  @endcanAccess
  @canAccess(\App\Models\Lan::class)
  @php $d = array_fill(0, $gi, 0); if ($physIdx >= 0) $d[$physIdx] = $lans; @endphp
  { label: "{!! trans('cruds.lan.title_short') !!}", data: [{{ implode(', ', $d) }}], value: {!! $lans !!}, url: "/admin/lans" }
  @endcanAccess
]},
treemap: {}
}

var topTags = [
    @canAccessAny(\App\Models\DataProcessing::class, \App\Models\SecurityControl::class)
    @canAccess(\App\Models\DataProcessing::class)
    {group:"{!! trans('cruds.menu.gdpr.title_short') !!}", tag:"{!! trans('cruds.dataProcessing.title') !!}", num:{!! $data_processing !!}, url: "/admin/data-processings" },
    @endcanAccess
    @canAccess(\App\Models\SecurityControl::class)
    {group:"{!! trans('cruds.menu.gdpr.title_short') !!}", tag:"{!! trans('cruds.securityControl.title_short') !!}", num:{!! $security_controls !!}, url: "/admin/security-controls" },
    @endcanAccess
    @endcanAccessAny
    @canAccessAny(\App\Models\Entity::class, \App\Models\Relation::class)
    @canAccess(\App\Models\Entity::class)
    {group:"{!! trans('cruds.menu.ecosystem.title_short') !!}", tag:"{!! trans('cruds.entity.title') !!}", num:{!! $entities !!}, url: "/admin/entities" },
    @endcanAccess
    @canAccess(\App\Models\Relation::class)
    {group:"{!! trans('cruds.menu.ecosystem.title_short') !!}", tag:"{!! trans('cruds.relation.title') !!}", num:{!! $relations !!}, url: "/admin/relations" },
    @endcanAccess
    @endcanAccessAny
    @canAccessAny(\App\Models\MacroProcessus::class, \App\Models\Process::class, \App\Models\Activity::class, \App\Models\Operation::class, \App\Models\Task::class, \App\Models\Actor::class, \App\Models\Information::class)
    @canAccess(\App\Models\MacroProcessus::class)
    {group:"{!! trans('cruds.menu.metier.title_short') !!}", tag:"{!! trans('cruds.macroProcessus.title') !!}", num: {!! $macroProcessuses !!}, url: "/admin/macro-processuses" },
    @endcanAccess
    @canAccess(\App\Models\Process::class)
    {group:"{!! trans('cruds.menu.metier.title_short') !!}", tag:"{!! trans('cruds.process.title') !!}", num:{!! $processes !!}, url: "/admin/processes" },
    @endcanAccess
    @canAccess(\App\Models\Activity::class)
    {group:"{!! trans('cruds.menu.metier.title_short') !!}", tag:"{!! trans('cruds.activity.title') !!}", num:{!! $activities !!}, url: "/admin/activities" },
    @endcanAccess
    @canAccess(\App\Models\Operation::class)
    {group:"{!! trans('cruds.menu.metier.title_short') !!}", tag:"{!! trans('cruds.operation.title') !!}", num:{!! $operations !!}, url: "/admin/operations" },
    @endcanAccess
    @canAccess(\App\Models\Task::class)
    {group:"{!! trans('cruds.menu.metier.title_short') !!}", tag:"{!! trans('cruds.task.title') !!}", num:{!! $tasks !!}, url: "/admin/tasks" },
    @endcanAccess
    @canAccess(\App\Models\Actor::class)
    {group:"{!! trans('cruds.menu.metier.title_short') !!}", tag:"{!! trans('cruds.actor.title') !!}", num:{!! $actors !!}, url: "/admin/actors" },
    @endcanAccess
    @canAccess(\App\Models\Information::class)
    {group:"{!! trans('cruds.menu.metier.title_short') !!}", tag:"{!! trans('cruds.information.title') !!}", num:{!! $informations !!}, url: "/admin/information" },
    @endcanAccess
    @endcanAccessAny
    @canAccessAny(\App\Models\ApplicationBlock::class, \App\Models\Application::class, \App\Models\ApplicationService::class, \App\Models\ApplicationModule::class, \App\Models\Database::class, \App\Models\ApplicationFlow::class)
    @canAccess(\App\Models\ApplicationBlock::class)
    {group:"{!! trans('cruds.menu.application.title') !!}", tag:"{!! trans('cruds.applicationBlock.title') !!}" , num:{!! $applicationBlocks !!}, url: "/admin/application-blocks" },
    @endcanAccess
    @canAccess(\App\Models\Application::class)
    {group:"{!! trans('cruds.menu.application.title') !!}", tag:"{!! trans('cruds.application.title') !!}", num:{!! $applications !!}, url: "/admin/applications" },
    @endcanAccess
    @canAccess(\App\Models\ApplicationService::class)
    {group:"{!! trans('cruds.menu.application.title') !!}", tag:"{!! trans('cruds.applicationService.title_short') !!}" , num:{!! $applicationServices !!}, url: "/admin/application-services" },
    @endcanAccess
    @canAccess(\App\Models\ApplicationModule::class)
    {group:"{!! trans('cruds.menu.application.title') !!}", tag:"{!! trans('cruds.applicationModule.title_short') !!}" , num:{!! $applicationModules !!}, url: "/admin/application-modules" },
    @endcanAccess
    @canAccess(\App\Models\Database::class)
    {group:"{!! trans('cruds.menu.application.title') !!}", tag:"{!! trans('cruds.database.title') !!}" , num:{!! $databases !!}, url: "/admin/databases" },
    @endcanAccess
    @canAccess(\App\Models\ApplicationFlow::class)
    {group:"{!! trans('cruds.menu.application.title') !!}", tag:"{!! trans('cruds.applicationFlow.title') !!}" , num:{!! $flows !!}, url: "/admin/application-flows" },
    @endcanAccess
    @endcanAccessAny
    @canAccessAny(\App\Models\ZoneAdmin::class, \App\Models\Annuaire::class, \App\Models\ForestAd::class, \App\Models\Domain::class)
    @canAccess(\App\Models\ZoneAdmin::class)
    {group:"{!! trans('cruds.menu.administration.title_short') !!}", tag:"{!! trans('cruds.zoneAdmin.title_short') !!}" , num:{!!$zones_ad!!}, url: "/admin/zone-admins" },
    @endcanAccess
    @canAccess(\App\Models\Annuaire::class)
    {group:"{!! trans('cruds.menu.administration.title_short') !!}", tag:"{!! trans('cruds.annuaire.title_short') !!}" , num:{!!$annuaires!!}, url: "/admin/annuaires" },
    @endcanAccess
    @canAccess(\App\Models\ForestAd::class)
    {group:"{!! trans('cruds.menu.administration.title_short') !!}", tag:"{!! trans('cruds.forestAd.title_short') !!}" , num:{!!$forests!!}, url: "/admin/forest-ads" },
    @endcanAccess
    @canAccess(\App\Models\Domain::class)
    {group:"{!! trans('cruds.menu.administration.title_short') !!}", tag:"{!! trans('cruds.domain.title_short') !!}" , num:{!!$domains!!}, url: "/admin/domains" },
    @endcanAccess
    @endcanAccessAny
    @canAccessAny(\App\Models\Network::class, \App\Models\Subnetwork::class, \App\Models\Gateway::class, \App\Models\ExternalConnectedEntity::class, \App\Models\NetworkSwitch::class, \App\Models\Router::class, \App\Models\SecurityDevice::class, \App\Models\Cluster::class, \App\Models\LogicalServer::class, \App\Models\Container::class, \App\Models\Vlan::class, \App\Models\Certificate::class)
    @canAccess(\App\Models\Network::class)
    {group:"{!! trans('cruds.menu.logical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.network.title') !!}" , num:{!! $networks !!}, url: "/admin/networks" },
    @endcanAccess
    @canAccess(\App\Models\Subnetwork::class)
    {group:"{!! trans('cruds.menu.logical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.subnetwork.title_short') !!}" , num:{!! $subnetworks !!}, url: "/admin/subnetworks" },
    @endcanAccess
    @canAccess(\App\Models\Gateway::class)
    {group:"{!! trans('cruds.menu.logical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.gateway.title_short') !!}" , num:{!! $gateways !!}, url: "/admin/gateways" },
    @endcanAccess
    @canAccess(\App\Models\ExternalConnectedEntity::class)
    {group:"{!! trans('cruds.menu.logical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.externalConnectedEntity.title_short') !!}" , num:{!! $externalConnectedEntities !!}, url: "/admin/external-connected-entities" },
    @endcanAccess
    @canAccess(\App\Models\NetworkSwitch::class)
    {group:"{!! trans('cruds.menu.logical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.networkSwitch.title_short') !!}" , num:{!! $switches !!}, url: "/admin/network-switches" },
    @endcanAccess
    @canAccess(\App\Models\Router::class)
    {group:"{!! trans('cruds.menu.logical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.router.title_short') !!}" , num:{!! $routers !!}, url: "/admin/routers" },
    @endcanAccess
    @canAccess(\App\Models\SecurityDevice::class)
    {group:"{!! trans('cruds.menu.logical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.securityDevice.title_short') !!}" , num:{!! $securityDevices !!}, url: "/admin/security-devices" },
    @endcanAccess
    @canAccess(\App\Models\Cluster::class)
    {group:"{!! trans('cruds.menu.logical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.cluster.title_short') !!}" , num:{!! $clusters !!}, url: "/admin/clusters" },
    @endcanAccess
    @canAccess(\App\Models\LogicalServer::class)
    {group:"{!! trans('cruds.menu.logical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.logicalServer.title_short') !!}" , num:{!! $logicalServers !!}, url: "/admin/logical-servers" },
    @endcanAccess
    @canAccess(\App\Models\Container::class)
    {group:"{!! trans('cruds.menu.logical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.container.title') !!}" , num:{!! $containers !!}, url: "/admin/containers" },
    @endcanAccess
    @canAccess(\App\Models\Vlan::class)
    {group:"{!! trans('cruds.menu.logical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.vlan.title_short') !!}" , num:{!! $vlans !!}, url: "/admin/vlans" },
    @endcanAccess
    @canAccess(\App\Models\Certificate::class)
    {group:"{!! trans('cruds.menu.logical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.certificate.title_short') !!}" , num:{!! $certificates !!}, url: "/admin/certificates" },
    @endcanAccess
    @endcanAccessAny
    @canAccessAny(\App\Models\Site::class, \App\Models\Building::class, \App\Models\Bay::class, \App\Models\Zone::class, \App\Models\PhysicalServer::class, \App\Models\Workstation::class, \App\Models\Phone::class, \App\Models\Peripheral::class, \App\Models\StorageDevice::class, \App\Models\PhysicalSwitch::class, \App\Models\PhysicalRouter::class, \App\Models\Wan::class, \App\Models\Man::class, \App\Models\Lan::class)
    @canAccess(\App\Models\Site::class)
    {group:"{!! trans('cruds.menu.physical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.site.title') !!}" , num: {!! $sites !!}, url: "/admin/sites" },
    @endcanAccess
    @canAccess(\App\Models\Building::class)
    {group:"{!! trans('cruds.menu.physical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.building.title') !!}" , num:{!! $buildings !!}, url: "/admin/buildings" },
    @endcanAccess
    @canAccess(\App\Models\Bay::class)
    {group:"{!! trans('cruds.menu.physical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.bay.title') !!}" , num:{!! $bays !!}, url: "/admin/bays" },
    @endcanAccess
    @canAccess(\App\Models\Zone::class)
    {group:"{!! trans('cruds.menu.physical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.zone.title_short') !!}" , num:{!! $zones !!}, url: "/admin/zones" },
    @endcanAccess
    @canAccess(\App\Models\PhysicalServer::class)
    {group:"{!! trans('cruds.menu.physical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.physicalServer.title_short') !!}", num:{!! $physicalServers !!}, url: "/admin/physical-servers" },
    @endcanAccess
    @canAccess(\App\Models\Workstation::class)
    {group:"{!! trans('cruds.menu.physical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.workstation.title') !!}" , num:{!! $workstations !!}, url: "/admin/workstations" },
    @endcanAccess
    @canAccess(\App\Models\Phone::class)
    {group:"{!! trans('cruds.menu.physical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.phone.title') !!}" , num:{!! $phones !!}, url: "/admin/phones" },
    @endcanAccess
    @canAccess(\App\Models\Peripheral::class)
    {group:"{!! trans('cruds.menu.physical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.peripheral.title') !!}" , num:{!! $peripherals !!}, url: "/admin/peripherals" },
    @endcanAccess
    @canAccess(\App\Models\StorageDevice::class)
    {group:"{!! trans('cruds.menu.physical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.storageDevice.title_short') !!}" , num:{!! $storageDevices !!}, url: "/admin/storage-devices" },
    @endcanAccess
    @canAccess(\App\Models\PhysicalSwitch::class)
    {group:"{!! trans('cruds.menu.physical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.physicalSwitch.title_short') !!}" , num:{!! $physicalSwitchs !!}, url: "/admin/physical-switches" },
    @endcanAccess
    @canAccess(\App\Models\PhysicalRouter::class)
    {group:"{!! trans('cruds.menu.physical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.physicalRouter.title_short') !!}" , num:{!! $physicalRouters !!}, url: "/admin/physical-routers" },
    @endcanAccess
    @canAccess(\App\Models\Wan::class)
    {group:"{!! trans('cruds.menu.physical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.wan.title_short') !!}" , num:{!! $wans !!}, url: "/admin/wans" },
    @endcanAccess
    @canAccess(\App\Models\Man::class)
    {group:"{!! trans('cruds.menu.physical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.man.title_short') !!}" , num:{!! $mans !!}, url: "/admin/mans" },
    @endcanAccess
    @canAccess(\App\Models\Lan::class)
    {group:"{!! trans('cruds.menu.physical_infrastructure.title_short') !!}", tag:"{!! trans('cruds.lan.title_short') !!}" , num:{!! $lans !!}, url: "/admin/lans" },
    @endcanAccess
    @endcanAccessAny
  ];

</script>

@vite(['resources/charts/chart-home.js'])

@parent
@endsection
