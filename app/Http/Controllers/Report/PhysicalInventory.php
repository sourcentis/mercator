<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Bay;
use App\Models\Building;
use App\Models\Peripheral;
use App\Models\Phone;
use App\Models\PhysicalRouter;
use App\Models\PhysicalSecurityDevice;
use App\Models\PhysicalServer;
use App\Models\PhysicalSwitch;
use App\Models\Site;
use App\Models\StorageDevice;
use App\Models\WifiTerminal;
use App\Models\Workstation;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Helper\Html;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class PhysicalInventory extends Controller
{
    /**
     * @throws Exception
     */
    public function generate(Request $request)
    {
        abort_if(Gate::denies('reports_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $inventory = [];

        // for all sites
        $sites = Site::All()->sortBy('name');
        foreach ($sites as $site) {
            $this->addToInventory($inventory, $site);

            // for all buildings
            $buildings = Building::query()
                ->where('site_id', '=', $site->id)
                ->orderBy('name')->get();
            foreach ($buildings as $building) {
                $this->addToInventory($inventory, $site, $building);

                // for all bays
                $bays = Bay::query()
                    ->where('building_id', '=', $building->id)
                    ->orderBy('name')->get();
                foreach ($bays as $bay) {
                    $this->addToInventory($inventory, $site, $building, $bay);
                }
            }
        }

        $header = [
            'Site',
            'Room',
            'Bay',
            'Asset',
            'Name',
            'Type',
            'Description',
        ];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$header], null, 'A1');

        // bold title
        $sheet->getStyle('1')->getFont()->setBold(true);

        // Widths
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);

        // converter
        $html = new Html;

        // Populate the Timesheet
        $row = 2;

        // create the sheet
        foreach ($inventory as $item) {
            $sheet->setCellValue("A{$row}", $item['site']);
            $sheet->setCellValue("B{$row}", $item['room']);
            $sheet->setCellValue("C{$row}", $item['bay']);
            $sheet->setCellValue("D{$row}", $item['category']);
            $sheet->setCellValue("E{$row}", $item['name']);
            $sheet->setCellValue("F{$row}", $item['type']);
            $sheet->setCellValue("G{$row}", $html->toRichTextObject($item['description']));

            $row++;
        }

        if ($request->get('format') == 'csv') {
            $writer = new Csv($spreadsheet);
            $path = storage_path('app/physicalInventory-'.Carbon::today()->format('Ymd').'.csv');
        } else {
            $writer = new Xlsx($spreadsheet);
            $path = storage_path('app/physicalInventory-'.Carbon::today()->format('Ymd').'.xlsx');
        }

        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    private function addToInventory(array &$inventory, Site $site, ?Building $building = null, ?Bay $bay = null): void
    {
        // PhysicalServer
        if ($bay != null) {
            $physicalServers = PhysicalServer::query()->where('bay_id', '=', $bay->id)->orderBy('name')->get();
        } elseif ($building != null) {
            $physicalServers = PhysicalServer::query()->where('bay_id', '=', null)->where('building_id', '=', $building->id)->orderBy('name')->get();
        } elseif ($site != null) {
            $physicalServers = PhysicalServer::query()->where('bay_id', '=', null)->where('building_id', '=', null)->where('site_id', '=', $site->id)->orderBy('name')->get();
        } else {
            $physicalServers = PhysicalServer::query()->orderBy('name')->get();
        }

        foreach ($physicalServers as $physicalServer) {
            $inventory[] = [
                'site' => $site->name ?? '',
                'room' => $building->name ?? '',
                'bay' => $bay->name ?? '',
                'category' => 'Server',
                'name' => $physicalServer->name,
                'type' => $physicalServer->type,
                'description' => $physicalServer->description,
            ];
        }

        // Workstation;
        if ($building != null) {
            $workstations = Workstation::query()->where('building_id', '=', $building->id)->orderBy('name')->get();
        } elseif ($site != null) {
            $workstations = Workstation::query()->where('building_id', '=', null)->where('site_id', '=', $site->id)->orderBy('name')->get();
        } else {
            $workstations = Workstation::query()->orderBy('name')->get();
        }

        foreach ($workstations as $workstation) {
            $inventory[] = [
                'site' => $site->name ?? '',
                'room' => $building->name ?? '',
                'bay' => '',
                'category' => 'Workstation',
                'name' => $workstation->name,
                'type' => $workstation->type,
                'description' => $workstation->description,
            ];
        }

        // StorageDevice;
        if ($bay !== null) {
            $storageDevices = StorageDevice::query()->where('bay_id', '=', $bay->id)->orderBy('name')->get();
        } elseif ($building != null) {
            $storageDevices = StorageDevice::query()->where('bay_id', '=', null)->where('building_id', '=', $building->id)->orderBy('name')->get();
        } elseif ($site != null) {
            $storageDevices = StorageDevice::query()->where('bay_id', '=', null)->where('building_id', '=', null)->where('site_id', '=', $site->id)->orderBy('name')->get();
        } else {
            $storageDevices = StorageDevice::query()->orderBy('name')->get();
        }

        foreach ($storageDevices as $storageDevice) {
            $inventory[] = [
                'site' => $site->name ?? '',
                'room' => $building->name ?? '',
                'bay' => $bay->name ?? '',
                'category' => 'Storage',
                'name' => $storageDevice->name,
                'type' => $storageDevice->name,
                'description' => $storageDevice->description,
            ];
        }

        // Peripheral
        if ($bay != null) {
            $peripherals = Peripheral::query()
                ->where('bay_id', '=', $bay->id)->orderBy('name')->get();
        } elseif ($building != null) {
            $peripherals = Peripheral::query()->where('bay_id', '=', null)->where('building_id', '=', $building->id)->orderBy('name')->get();
        } elseif ($site != null) {
            $peripherals = Peripheral::query()->where('bay_id', '=', null)->where('building_id', '=', null)->where('site_id', '=', $site->id)->orderBy('name')->get();
        } else {
            $peripherals = Peripheral::query()->orderBy('name')->get();
        }

        foreach ($peripherals as $peripheral) {
            $inventory[] = [
                'site' => $site->name ?? '',
                'room' => $building->name ?? '',
                'bay' => $bay->name ?? '',
                'category' => 'Peripheral',
                'name' => $peripheral->name,
                'type' => $peripheral->type,
                'description' => $peripheral->description,
            ];
        }

        // Phone
        if ($building != null) {
            $phones = Phone::query()->where('building_id', '=', $building->id)->orderBy('name')->get();
        } elseif ($site != null) {
            $phones = Phone::query()->where('building_id', '=', null)->where('site_id', '=', $site->id)->orderBy('name')->get();
        } else {
            $phones = Phone::query()->orderBy('name')->get();
        }

        foreach ($phones as $phone) {
            $inventory[] = [
                'site' => $site->name ?? '',
                'room' => $building->name ?? '',
                'bay' => '',
                'category' => 'Phone',
                'name' => $phone->name,
                'type' => $phone->type,
                'description' => $phone->description,
            ];
        }

        // PhysicalSwitch
        if ($bay != null) {
            $physicalSwitches = PhysicalSwitch::query()->where('bay_id', '=', $bay->id)->orderBy('name')->get();
        } elseif ($building != null) {
            $physicalSwitches = PhysicalSwitch::query()->where('bay_id', '=', null)->where('building_id', '=', $building->id)->orderBy('name')->get();
        } elseif ($site != null) {
            $physicalSwitches = PhysicalSwitch::query()->where('bay_id', '=', null)->where('building_id', '=', null)->where('site_id', '=', $site->id)->orderBy('name')->get();
        } else {
            $physicalSwitches = PhysicalSwitch::query()->orderBy('name')->get();
        }

        foreach ($physicalSwitches as $physicalSwitch) {
            $inventory[] = [
                'site' => $site->name ?? '',
                'room' => $building->name ?? '',
                'bay' => $bay->name ?? '',
                'category' => 'Switch',
                'name' => $physicalSwitch->name,
                'type' => $physicalSwitch->type,
                'description' => $physicalSwitch->description,
            ];
        }

        // PhysicalRouter
        if ($bay != null) {
            $physicalRouters = PhysicalRouter::query()->where('bay_id', '=', $bay->id)->orderBy('name')->get();
        } elseif ($building != null) {
            $physicalRouters = PhysicalRouter::query()->where('bay_id', '=', null)->where('building_id', '=', $building->id)->orderBy('name')->get();
        } elseif ($site != null) {
            $physicalRouters = PhysicalRouter::query()->where('bay_id', '=', null)->where('building_id', '=', null)->where('site_id', '=', $site->id)->orderBy('name')->get();
        } else {
            $physicalRouters = PhysicalRouter::query()->orderBy('name')->get();
        }

        foreach ($physicalRouters as $physicalRouter) {
            $inventory[] = [
                'site' => $site->name ?? '',
                'room' => $building->name ?? '',
                'bay' => $bay->name ?? '',
                'category' => 'Router',
                'name' => $physicalRouter->name,
                'type' => $physicalRouter->type,
                'description' => $physicalRouter->description,
            ];
        }

        // WifiTerminal
        if ($building != null) {
            $wifiTerminals = WifiTerminal::where('building_id', '=', $building->id)->orderBy('name')->get();
        } elseif ($site != null) {
            $wifiTerminals = WifiTerminal::where('building_id', '=', null)->where('site_id', '=', $site->id)->orderBy('name')->get();
        } else {
            $wifiTerminals = WifiTerminal::orderBy('name')->get();
        }

        foreach ($wifiTerminals as $wifiTerminal) {
            $inventory[] = [
                'site' => $site->name ?? '',
                'room' => $building->name ?? '',
                'bay' => '',
                'category' => 'Wifi',
                'name' => $wifiTerminal->name,
                'type' => $wifiTerminal->type,
                'description' => $wifiTerminal->description,
            ];
        }

        // Physical Security Devices
        if ($bay != null) {
            $physicalSecurityDevices = PhysicalSecurityDevice::query()->where('bay_id', '=', $bay->id)->orderBy('name')->get();
        } elseif ($building != null) {
            $physicalSecurityDevices = PhysicalSecurityDevice::query()->where('bay_id', '=', null)->where('building_id', '=', $building->id)->orderBy('name')->get();
        } elseif ($site != null) {
            $physicalSecurityDevices = PhysicalSecurityDevice::query()->where('bay_id', '=', null)->where('building_id', '=', null)->where('site_id', '=', $site->id)->orderBy('name')->get();
        } else {
            $physicalSecurityDevices = PhysicalSecurityDevice::query()->orderBy('name')->get();
        }

        foreach ($physicalSecurityDevices as $physicalSecurityDevice) {
            $inventory[] = [
                'site' => $site->name ?? '',
                'room' => $building->name ?? '',
                'bay' => $bay->name ?? '',
                'category' => 'Sécurité',
                'name' => $physicalSecurityDevice->name,
                'type' => $physicalSecurityDevice->type,
                'description' => $physicalSecurityDevice->description,
            ];
        }
    }
}
