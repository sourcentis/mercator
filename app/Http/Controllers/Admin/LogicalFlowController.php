<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyLogicalFlowRequest;
use App\Http\Requests\StoreLogicalFlowRequest;
use App\Http\Requests\UpdateLogicalFlowRequest;
use App\Rules\Cidr;
use Gate;
use Illuminate\Support\Collection;
use App\Models\Cluster;
use App\Models\LogicalFlow;
use App\Models\LogicalServer;
use App\Models\Peripheral;
use App\Models\PhysicalSecurityDevice;
use App\Models\PhysicalServer;
use App\Models\Router;
use App\Models\SecurityDevice;
use App\Models\StorageDevice;
use App\Models\Subnetwork;
use App\Models\Workstation;
use Symfony\Component\HttpFoundation\Response;

class LogicalFlowController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('logical_flow_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logicalFlows = LogicalFlow::with([
            'router',
            'logicalServerSource',
            'peripheralSource',
            'physicalServerSource',
            'storageDeviceSource',
            'workstationSource',
            'physicalSecurityDeviceSource',
            'clusterSource',
            'subnetworkSource',
            'logicalServerDest',
            'peripheralDest',
            'physicalServerDest',
            'storageDeviceDest',
            'workstationDest',
            'physicalSecurityDeviceDest',
            'subnetworkDest',
            'clusterDest',
        ])
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    foreach (LogicalFlow::$searchable as $field) {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                });
            })
            ->orderby('name')
            ->paginate(min(max((int) request('per_page', 50), 10), 500));

        return view('admin.logicalFlows.index', compact('logicalFlows'));
    }

    public function create()
    {
        abort_if(Gate::denies('logical_flow_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $routers = Router::query()->orderBy('name')->pluck('name', 'id');

        // Get Equipments
        $logicalServers = LogicalServer::query()->orderBy('name')->pluck('name', 'id');
        $peripherals = Peripheral::query()->orderBy('name')->pluck('name', 'id');
        $physicalServers = PhysicalServer::query()->orderBy('name')->pluck('name', 'id');
        $storageDevices = StorageDevice::query()->orderBy('name')->pluck('name', 'id');
        $workstations = Workstation::query()->orderBy('name')->pluck('name', 'id');
        $securityDevices = SecurityDevice::query()->orderBy('name')->pluck('name', 'id');
        $physicalSecurityDevices = PhysicalSecurityDevice::query()->orderBy('name')->pluck('name', 'id');
        $subnetworks = Subnetwork::query()->orderBy('name')->pluck('name', 'id');
        $clusters = Cluster::query()->orderBy('name')->pluck('name', 'id');

        // Build device list
        $devices = Collection::make();
        foreach ($logicalServers as $key => $value) {
            $devices->put(LogicalServer::$prefix . $key, $value);
        }
        foreach ($peripherals as $key => $value) {
            $devices->put(Peripheral::$prefix . $key, $value);
        }
        foreach ($physicalServers as $key => $value) {
            $devices->put(PhysicalServer::$prefix . $key, $value);
        }
        foreach ($storageDevices as $key => $value) {
            $devices->put(StorageDevice::$prefix . $key, $value);
        }
        foreach ($workstations as $key => $value) {
            $devices->put(Workstation::$prefix . $key, $value);
        }
        foreach ($physicalSecurityDevices as $key => $value) {
            $devices->put(PhysicalSecurityDevice::$prefix . $key, $value);
        }
        foreach ($securityDevices as $key => $value) {
            $devices->put(SecurityDevice::$prefix . $key, $value);
        }
        foreach ($subnetworks as $key => $value) {
            $devices->put(Subnetwork::$prefix . $key, $value);
        }
        foreach ($clusters as $key => $value) {
            $devices->put(Cluster::$prefix . $key, $value);
        }

        // Lists
        $interface_list = LogicalFlow::query()
            ->select('interface')
            ->whereNotNull('interface')
            ->distinct()
            ->orderBy('interface')
            ->pluck('interface');
        $protocol_list = LogicalFlow::query()
            ->select('protocol')
            ->whereNotNull('protocol')
            ->distinct()
            ->orderBy('protocol')
            ->pluck('protocol');

        return view(
            'admin.logicalFlows.create',
            compact('routers', 'devices',
                'interface_list', 'protocol_list')
        );
    }

    public function store(StoreLogicalFlowRequest $request)
    {
        $this->validate($request, [
                'source_ip_range' => [
                    new Cidr,
                    'nullable',
                    'required_without:src_id',
                ],
                'priority' => [
                    'nullable',
                    'integer',
                ],
                'src_id' => [
                    'nullable',
                    'required_without:source_ip_range',
                ],
                'dest_ip_range' => [
                    new Cidr,
                    'nullable',
                    'required_without:dest_id',
                ],
                'dest_id' => [
                    'nullable',
                    'required_without:dest_ip_range',
                ]
            ]
        );

        $link = LogicalFlow::create($request->all());

        // Source device
        if (str_starts_with($request->src_id, LogicalServer::$prefix)) {
            $link->logical_server_source_id = intval(substr($request->src_id, strlen(LogicalServer::$prefix)));
        } elseif (str_starts_with($request->src_id, Peripheral::$prefix)) {
            $link->peripheral_source_id = intval(substr($request->src_id, strlen(Peripheral::$prefix)));
        } elseif (str_starts_with($request->src_id, PhysicalServer::$prefix)) {
            $link->physical_server_source_id = intval(substr($request->src_id, strlen(PhysicalServer::$prefix)));
        } elseif (str_starts_with($request->src_id, StorageDevice::$prefix)) {
            $link->storage_device_source_id = intval(substr($request->src_id, strlen(StorageDevice::$prefix)));
        } elseif (str_starts_with($request->src_id, Workstation::$prefix)) {
            $link->workstation_source_id = intval(substr($request->src_id, strlen(Workstation::$prefix)));
        } elseif (str_starts_with($request->src_id, PhysicalSecurityDevice::$prefix)) {
            $link->physical_security_device_source_id = intval(substr($request->src_id, strlen(PhysicalSecurityDevice::$prefix)));
        } elseif (str_starts_with($request->src_id, SecurityDevice::$prefix)) {
            $link->security_device_source_id = intval(substr($request->src_id, strlen(SecurityDevice::$prefix)));
        } elseif (str_starts_with($request->src_id, Subnetwork::$prefix)) {
            $link->subnetwork_source_id = intval(substr($request->src_id, strlen(Subnetwork::$prefix)));
        } elseif (str_starts_with($request->src_id, Cluster::$prefix)) {
            $link->cluster_source_id = intval(substr($request->src_id, strlen(Cluster::$prefix)));
        }

        // Dest device
        if (str_starts_with($request->dest_id, LogicalServer::$prefix)) {
            $link->logical_server_dest_id = intval(substr($request->dest_id, strlen(LogicalServer::$prefix)));
        } elseif (str_starts_with($request->dest_id, Peripheral::$prefix)) {
            $link->peripheral_dest_id = intval(substr($request->dest_id, strlen(Peripheral::$prefix)));
        } elseif (str_starts_with($request->dest_id, PhysicalServer::$prefix)) {
            $link->physical_server_dest_id = intval(substr($request->dest_id, strlen(PhysicalServer::$prefix)));
        } elseif (str_starts_with($request->dest_id, StorageDevice::$prefix)) {
            $link->storage_device_dest_id = intval(substr($request->dest_id, strlen(StorageDevice::$prefix)));
        } elseif (str_starts_with($request->dest_id, Workstation::$prefix)) {
            $link->workstation_dest_id = intval(substr($request->dest_id, strlen(Workstation::$prefix)));
        } elseif (str_starts_with($request->dest_id, PhysicalSecurityDevice::$prefix)) {
            $link->physical_security_device_dest_id = intval(substr($request->dest_id, strlen(PhysicalSecurityDevice::$prefix)));
        } elseif (str_starts_with($request->dest_id, SecurityDevice::$prefix)) {
            $link->security_device_dest_id = intval(substr($request->dest_id, strlen(SecurityDevice::$prefix)));
        } elseif (str_starts_with($request->dest_id, Subnetwork::$prefix)) {
            $link->subnetwork_dest_id = intval(substr($request->dest_id, strlen(Subnetwork::$prefix)));
        } elseif (str_starts_with($request->dest_id, Cluster::$prefix)) {
            $link->cluster_dest_id = intval(substr($request->dest_id, strlen(Cluster::$prefix)));
        }

        $link->update();

        return redirect()->route('admin.logical-flows.index');
    }

    public function edit(LogicalFlow $logicalFlow)
    {
        abort_if(Gate::denies('logical_flow_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $routers = Router::query()->orderBy('name')->pluck('name', 'id');

        // Get Equipments
        $logicalServers = LogicalServer::query()->orderBy('name')->pluck('name', 'id');
        $peripherals = Peripheral::query()->orderBy('name')->pluck('name', 'id');
        $physicalServers = PhysicalServer::query()->orderBy('name')->pluck('name', 'id');
        $storageDevices = StorageDevice::query()->orderBy('name')->pluck('name', 'id');
        $workstations = Workstation::query()->orderBy('name')->pluck('name', 'id');
        $physicalSecurityDevices = PhysicalSecurityDevice::query()->orderBy('name')->pluck('name', 'id');
        $securityDevices = SecurityDevice::query()->orderBy('name')->pluck('name', 'id');
        $subnetworks = Subnetwork::query()->orderBy('name')->pluck('name', 'id');
        $clusters = Cluster::query()->orderBy('name')->pluck('name', 'id');

        // Build device list
        $devices = Collection::make();
        foreach ($logicalServers as $key => $value) {
            $devices->put(LogicalServer::$prefix . $key, $value);
        }
        foreach ($peripherals as $key => $value) {
            $devices->put(Peripheral::$prefix . $key, $value);
        }
        foreach ($physicalServers as $key => $value) {
            $devices->put(PhysicalServer::$prefix . $key, $value);
        }
        foreach ($storageDevices as $key => $value) {
            $devices->put(StorageDevice::$prefix . $key, $value);
        }
        foreach ($workstations as $key => $value) {
            $devices->put(Workstation::$prefix . $key, $value);
        }
        foreach ($physicalSecurityDevices as $key => $value) {
            $devices->put(PhysicalSecurityDevice::$prefix . $key, $value);
        }
        foreach ($securityDevices as $key => $value) {
            $devices->put(SecurityDevice::$prefix . $key, $value);
        }
        foreach ($subnetworks as $key => $value) {
            $devices->put(Subnetwork::$prefix . $key, $value);
        }
        foreach ($clusters as $key => $value) {
            $devices->put(Cluster::$prefix . $key, $value);
        }

        // Lists
        $interface_list = LogicalFlow::select('interface')->whereNotNull('interface')->distinct()->orderBy('interface')->pluck('interface');
        $protocol_list = LogicalFlow::select('protocol')->whereNotNull('protocol')->distinct()->orderBy('protocol')->pluck('protocol');

        return view(
            'admin.logicalFlows.edit',
            compact('logicalFlow', 'devices', 'routers',
                'interface_list', 'protocol_list')
        );
    }

    public function update(UpdateLogicalFlowRequest $request, LogicalFlow $logicalFlow)
    {
        $this->validate($request, [
                'source_ip_range' => [
                    new Cidr,
                    'nullable',
                    'required_without:src_id',
                ],
                'priority' => [
                    'nullable',
                    'integer',
                ],
                'src_id' => [
                    'nullable',
                    'required_without:source_ip_range',
                ],
                'dest_ip_range' => [
                    new Cidr,
                    'nullable',
                    'required_without:dest_id',
                ],
                'dest_id' => [
                    'nullable',
                    'required_without:dest_ip_range',
                ]
            ]
        );

        // Source device
        if (str_starts_with($request->src_id, LogicalServer::$prefix)) {
            $logicalFlow->logical_server_source_id = intval(substr($request->src_id, strlen(LogicalServer::$prefix)));
        } elseif (str_starts_with($request->src_id, Peripheral::$prefix)) {
            $logicalFlow->peripheral_source_id = intval(substr($request->src_id, strlen(Peripheral::$prefix)));
        } elseif (str_starts_with($request->src_id, PhysicalServer::$prefix)) {
            $logicalFlow->physical_server_source_id = intval(substr($request->src_id, strlen(PhysicalServer::$prefix)));
        } elseif (str_starts_with($request->src_id, StorageDevice::$prefix)) {
            $logicalFlow->storage_device_source_id = intval(substr($request->src_id, strlen(StorageDevice::$prefix)));
        } elseif (str_starts_with($request->src_id, Workstation::$prefix)) {
            $logicalFlow->workstation_source_id = intval(substr($request->src_id, strlen(Workstation::$prefix)));
        } elseif (str_starts_with($request->src_id, PhysicalSecurityDevice::$prefix)) {
            $logicalFlow->physical_security_device_source_id = intval(substr($request->src_id, strlen(PhysicalSecurityDevice::$prefix)));
        } elseif (str_starts_with($request->src_id, SecurityDevice::$prefix)) {
            $logicalFlow->security_device_source_id = intval(substr($request->src_id, strlen(SecurityDevice::$prefix)));
        } elseif (str_starts_with($request->src_id, Subnetwork::$prefix)) {
            $logicalFlow->subnetwork_source_id = intval(substr($request->src_id, strlen(Subnetwork::$prefix)));
        } elseif (str_starts_with($request->src_id, Cluster::$prefix)) {
            $logicalFlow->cluster_source_id = intval(substr($request->src_id, strlen(Cluster::$prefix)));
        }

        // Dest device
        if (str_starts_with($request->dest_id, LogicalServer::$prefix)) {
            $logicalFlow->logical_server_dest_id = intval(substr($request->dest_id, strlen(LogicalServer::$prefix)));
        } elseif (str_starts_with($request->dest_id, Peripheral::$prefix)) {
            $logicalFlow->peripheral_dest_id = intval(substr($request->dest_id, strlen(Peripheral::$prefix)));
        } elseif (str_starts_with($request->dest_id, PhysicalServer::$prefix)) {
            $logicalFlow->physical_server_dest_id = intval(substr($request->dest_id, strlen(PhysicalServer::$prefix)));
        } elseif (str_starts_with($request->dest_id, StorageDevice::$prefix)) {
            $logicalFlow->storage_device_dest_id = intval(substr($request->dest_id, strlen(StorageDevice::$prefix)));
        } elseif (str_starts_with($request->dest_id, Workstation::$prefix)) {
            $logicalFlow->workstation_dest_id = intval(substr($request->dest_id, strlen(Workstation::$prefix)));
        } elseif (str_starts_with($request->dest_id, PhysicalSecurityDevice::$prefix)) {
            $logicalFlow->physical_security_device_dest_id = intval(substr($request->dest_id, strlen(PhysicalSecurityDevice::$prefix)));
        } elseif (str_starts_with($request->dest_id, SecurityDevice::$prefix)) {
            $logicalFlow->security_device_dest_id = intval(substr($request->dest_id, strlen(SecurityDevice::$prefix)));
        } elseif (str_starts_with($request->dest_id, Subnetwork::$prefix)) {
            $logicalFlow->subnetwork_dest_id = intval(substr($request->dest_id, strlen(Subnetwork::$prefix)));
        } elseif (str_starts_with($request->dest_id, Cluster::$prefix)) {
            $logicalFlow->cluster_dest_id = intval(substr($request->dest_id, strlen(Cluster::$prefix)));
        }

        $logicalFlow->update($request->all());

        return redirect()->route('admin.logical-flows.index');
    }

    public function show(LogicalFlow $logicalFlow)
    {
        abort_if(Gate::denies('logical_flow_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.logicalFlows.show', compact('logicalFlow'));
    }

    public function destroy(LogicalFlow $logicalFlow)
    {
        abort_if(Gate::denies('logical_flow_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logicalFlow->delete();

        return redirect()->route('admin.logical-flows.index');
    }

    public function massDestroy(MassDestroyLogicalFlowRequest $request)
    {
        LogicalFlow::query()->whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}