@extends('layouts.admin')

@section('title')
    {{ trans('panel.menu.schema') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            {{ trans('panel.menu.schema') }}
        </div>
        <div class="card-body">
            <div class="graphviz" id="graph"></div>
        </div>
    </div>
@endsection

@section('scripts')
@vite(['resources/js/graphviz.js'])
<script @nonce>
document.addEventListener('graphvizReady', () => {
    document.getElementById("graph").innerHTML = window.graphviz.layout(
        `digraph {
        pencolor="#7c123e"
        penwidth=2
        subgraph clusterRGPR {
          label="{{ trans('cruds.report.lists.gdpr') }}"
          fontsize=16
          graph[style=dotted];
          href="/admin/report/ecosystem"
          shape = "Mrecord"
          CONTROL [label="{{ trans('cruds.securityControl.title') }}" shape=none labelloc="b"  width=1 height=1.3 image="/images/security-control.png" href="/admin/security-controls"]
          REGISTER [label="{{ trans('cruds.dataProcessing.title') }}" shape=none labelloc="b"  width=1 height=1.3 image="/images/register.png" href="/admin/data-processings"]
          }
        subgraph clusterA {
          label="{{ trans('cruds.report.cartography.ecosystem') }}"
          fontsize=16
          graph[style=dotted];
          href="/admin/report/ecosystem"
          shape = "Mrecord"
          ENTITY [label="{{ trans('cruds.entity.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/entity.png" href="/admin/entities"]
          RELATION [label="{{ trans('cruds.relation.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/relation.png" href="/admin/relations"]
          }
        subgraph clusterB {
          label="{{ trans('cruds.report.cartography.information_system') }}"
          fontsize=16
          graph[style=dotted];
          href="/admin/report/information_system"
          MPROCESS [label="{{ trans('cruds.macroProcessus.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/macroprocess.png" href="/admin/macro-processuses"]
          PROCESS [label="{{ trans('cruds.process.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/process.png" href="/admin/processes"]
          ACTIVITY [label="{{ trans('cruds.activity.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/activity.png" href="/admin/activities"]
          OPERATION [label="{{ trans('cruds.operation.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/operation.png" href="/admin/operations"]
          TASK [label="{{ trans('cruds.task.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/task.png" href="/admin/tasks"]
          ACTOR [label="{{ trans('cruds.actor.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/actor.png" href="/admin/actors"]
          INFORMATION [label="{{ trans('cruds.information.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/information.png" href="/admin/information"]
          }
        subgraph clusterC {
          label="{{ trans('cruds.report.cartography.applications') }}"
          fontsize=16
          graph[style=dotted];
          href="/admin/report/applications"
          APPLICBLOCK [label="{{ trans('cruds.applicationBlock.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/applicationblock.png" href="/admin/application-blocks"]
          APPLICATION [label="{{ trans('cruds.application.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/application.png" href="/admin/applications"]
          APPLICSERV [label="{{ trans('cruds.applicationService.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/applicationservice.png" href="/admin/application-services"]
          APPLICMODULE [label="{{ trans('cruds.applicationModule.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/applicationmodule.png" href="/admin/application-modules"]
          DATABASE [label="{{ trans('cruds.database.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/database.png" href="/admin/databases"]
          }
        subgraph clusterD {
          label="{{ trans('cruds.report.cartography.logical_infrastructure') }}"
          fontsize=16
          graph[style=dotted];
          href="/admin/report/logical_infrastructure"
          EXTERNAL [label="{{ trans('cruds.externalConnectedEntity.title_short') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/entity.png" href="/admin/external-connected-entities"]
          NETWORK [label="{{ trans('cruds.network.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/cloud.png" href="/admin/networks"]
          SUBNETWORK [label="{{ trans('cruds.subnetwork.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/network.png" href="/admin/subnetworks"]
          VLAN [label="vlan" shape=none labelloc="b"  width=1 height=1.1 image="/images/vlan.png" href="/admin/vlans"]
          LOGICALSERVER [label="{{ trans('cruds.logicalServer.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/lserver.png" href="/admin/logical-servers"]
          LOGICALROUTER [label="{{ trans('cruds.router.title_short') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/router.png" href="/admin/routers"]
          NETWORKSWITCHES [label="{{ trans('cruds.networkSwitch.title_short') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/switch.png" href="/admin/network-switches"]
          CERTIFICATE [label="{{ trans('cruds.certificate.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/certificate.png" href="/admin/certificates"]
          }
        subgraph clusterE {
          label="{{ trans('cruds.report.cartography.physical_infrastructure') }}"
          fontsize=16
          graph[style=dotted];
          href="/admin/report/physical_infrastructure"
          SITE [label="{{ trans('cruds.site.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/site.png" href="/admin/sites"]
          BUILDING [label="{{ trans('cruds.building.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/building.png" href="/admin/buildings"]
          BAY [label="{{ trans('cruds.bay.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/bay.png" href="/admin/bays"]
          PHYSICALSERVER [label="{{ trans('cruds.physicalServer.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/server.png" href="/admin/physical-servers"]
          WORKSTATION [label="{{ trans('cruds.workstation.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/workstation.png" href="/admin/workstations"]
          PERIPHERAL [label="{{ trans('cruds.peripheral.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/peripheral.png" href="/admin/peripherals"]
          WIFI [label="{{ trans('cruds.wifiTerminal.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/wifi.png" href="/admin/wifi-terminals"]
          PHONE [label="{{ trans('cruds.phone.title') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/phone.png" href="/admin/phones"]
          SWITCH [label="{{ trans('cruds.physicalSwitch.title_short') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/switch.png" href="/admin/physical-switches"]
          STORAGE [label="{{ trans('cruds.storageDevice.title_short') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/storage.png" href="/admin/storage-devices"]
          ROUTER [label="{{ trans('cruds.physicalRouter.title_short') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/router.png" href="/admin/physical-routers"]
          SECURITY [label="{{ trans('cruds.physicalSecurityDevice.title_short') }}" shape=none labelloc="b"  width=1 height=1.1 image="/images/securitydevice.png" href="/admin/physical-security-devices"]
          }

          MPROCESS -> PROCESS  [label="  0-n"]
          PROCESS -> ACTIVITY  [label="  0-n"]
          PROCESS -> OPERATION  [label="  0-n"]
          ENTITY -> PROCESS [label="  0-n"]
          PROCESS -> APPLICATION  [label="  n-m"]
          PROCESS -> INFORMATION  [label="  n-m"]
          ACTIVITY -> OPERATION  [label="  0-n"]
          OPERATION -> TASK  [label="  0-n"]
          OPERATION -> ACTOR  [label="  0-n"]

          APPLICBLOCK -> APPLICATION  [label="  0-n"]
          ENTITY -> APPLICATION  [label="  0-n"]
          APPLICATION -> APPLICSERV  [label="  0-n"]
          APPLICSERV-> APPLICMODULE  [label="  0-n"]
          INFORMATION -> DATABASE [label="  n-m"]
          APPLICATION -> DATABASE   [label="  n-m"]

          EXTERNAL -> NETWORK [label="  0-n"]
          NETWORK -> SUBNETWORK [label="  0-n"]
          SUBNETWORK -> LOGICALSERVER [label="  0-n"]
          SUBNETWORK -> VLAN [label="  0-n"]
          SUBNETWORK -> LOGICALROUTER [label="  0-n"]
          SUBNETWORK -> NETWORKSWITCHES [label="  0-n"]
          LOGICALSERVER -> PHYSICALSERVER [label="  n-m"]
          APPLICATION -> LOGICALSERVER [label="  n-m"]
          CERTIFICATE -> LOGICALSERVER [label="  0-n"]
          CERTIFICATE -> APPLICATION [label="  0-n"]

          ENTITY -> RELATION  [label="  0-n"]

          SITE -> BUILDING [label="  0-n"]
          BUILDING -> BAY [label="  0-n"]
          BUILDING -> WORKSTATION [label="  0-n"]
          BUILDING -> PERIPHERAL [label="  0-n"]
          BUILDING -> PHONE [label="  0-n"]
          BAY -> PHYSICALSERVER [label="  0-n"]
          BAY -> STORAGE [label="  0-n"]
          BAY -> SWITCH [label="  0-n"]
          BAY -> ROUTER [label="  0-n"]
          BAY -> SECURITY [label="  0-n"]
          BUILDING -> WIFI [label="  0-n"]
}`,
        "svg",
        "dot",
        {
            images: [
                { path: "/images/register.png",          width: "64px", height: "64px" },
                { path: "/images/dataprocessing.png",    width: "64px", height: "64px" },
                { path: "/images/security-control.png",  width: "64px", height: "64px" },
                { path: "/images/entity.png",            width: "64px", height: "64px" },
                { path: "/images/relation.png",          width: "64px", height: "64px" },
                { path: "/images/macroprocess.png",      width: "64px", height: "64px" },
                { path: "/images/process.png",           width: "64px", height: "64px" },
                { path: "/images/activity.png",          width: "64px", height: "64px" },
                { path: "/images/operation.png",         width: "64px", height: "64px" },
                { path: "/images/task.png",              width: "64px", height: "64px" },
                { path: "/images/actor.png",             width: "64px", height: "64px" },
                { path: "/images/information.png",       width: "64px", height: "64px" },
                { path: "/images/applicationblock.png",  width: "64px", height: "64px" },
                { path: "/images/application.png",       width: "64px", height: "64px" },
                { path: "/images/applicationservice.png",width: "64px", height: "64px" },
                { path: "/images/applicationmodule.png", width: "64px", height: "64px" },
                { path: "/images/database.png",          width: "64px", height: "64px" },
                { path: "/images/cloud.png",             width: "64px", height: "64px" },
                { path: "/images/network.png",           width: "64px", height: "64px" },
                { path: "/images/router.png",            width: "64px", height: "64px" },
                { path: "/images/site.png",              width: "64px", height: "64px" },
                { path: "/images/building.png",          width: "64px", height: "64px" },
                { path: "/images/bay.png",               width: "64px", height: "64px" },
                { path: "/images/server.png",            width: "64px", height: "64px" },
                { path: "/images/workstation.png",       width: "64px", height: "64px" },
                { path: "/images/storage.png",           width: "64px", height: "64px" },
                { path: "/images/peripheral.png",        width: "64px", height: "64px" },
                { path: "/images/phone.png",             width: "64px", height: "64px" },
                { path: "/images/switch.png",            width: "64px", height: "64px" },
                { path: "/images/wifi.png",              width: "64px", height: "64px" },
                { path: "/images/certificate.png",       width: "64px", height: "64px" },
                { path: "/images/security.png",          width: "64px", height: "64px" },
                { path: "/images/securitydevice.png",    width: "64px", height: "64px" },
                { path: "/images/lserver.png",           width: "64px", height: "64px" },
                { path: "/images/vlan.png",              width: "64px", height: "64px" },
            ]
        }
    );

    // Remplace .width().height().fit(true) : le SVG occupe tout le conteneur
    const svg = document.querySelector('#graph svg');
    if (svg) {
        svg.style.width  = '100%';
        svg.style.height = '100%';
    }
});
</script>
@parent
@endsection