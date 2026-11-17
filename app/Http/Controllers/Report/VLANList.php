<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\LogicalServer;
use App\Models\NetworkSwitch;
use App\Models\PhysicalServer;
use App\Models\Vlan;
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

class VLANList extends Controller
{
    /**
     * @throws Exception
     */
    public function generate(Request $request)
    {
        abort_if(Gate::denies('reports_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $vlans = Vlan::query()->orderBy('vlans.name')->get();
        $vlans->load('subnetworks');

        $lservers = LogicalServer::orderBy('name')->get();
        $pservers = PhysicalServer::orderBy('name')->get();
        $switches = NetworkSwitch::orderBy('name')->get();
        $workstations = Workstation::orderBy('name')->get();

        $header = [
            'Name',
            'VLAN-ID',
            'Description',
            'subnet name',
            'subnet address',
            'Logical servers',
            'Physical servers',
            'Logical switches',
            'Workstations',
        ];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$header], null, 'A1');

        // bold title
        $sheet->getStyle('1')->getFont()->setBold(true);

        // Widths
        $sheet->getColumnDimension('A')->setAutoSize(true); // Name
        $sheet->getColumnDimension('B')->setWidth(30, 'pt'); // VLAN-ID
        $sheet->getColumnDimension('C')->setWidth(200, 'pt'); // Desc
        $sheet->getColumnDimension('D')->setWidth(100, 'pt'); // subnets name
        $sheet->getColumnDimension('E')->setWidth(100, 'pt'); // subnets address
        $sheet->getColumnDimension('F')->setWidth(200, 'pt'); // logical servers
        $sheet->getColumnDimension('G')->setWidth(200, 'pt'); // physical servers
        $sheet->getColumnDimension('H')->setWidth(200, 'pt'); // switches
        $sheet->getColumnDimension('I')->setWidth(200, 'pt'); // workstations

        // wordwrap
        $sheet->getStyle('F')->getAlignment()->setWrapText(true);
        $sheet->getStyle('G')->getAlignment()->setWrapText(true);
        $sheet->getStyle('H')->getAlignment()->setWrapText(true);
        $sheet->getStyle('I')->getAlignment()->setWrapText(true);

        // converter
        $html = new Html;

        // Populate the Timesheet
        $row = 2;

        // create the sheet
        foreach ($vlans as $vlan) {
            $sheet->setCellValue("A{$row}", $vlan->name);
            $sheet->setCellValue("B{$row}", $vlan->vlan_id);
            $sheet->setCellValue("C{$row}", $html->toRichTextObject($vlan->description));

            // Subnets
            foreach ($vlan->subnetworks as $subnet) {
                if ($vlan->subnetworks->first() !== $subnet) {
                    $sheet->setCellValue("A{$row}", $vlan->name);
                    $sheet->setCellValue("C{$row}", $html->toRichTextObject($vlan->description));
                }
                $sheet->setCellValue("D{$row}", $subnet->name);
                $sheet->setCellValue("E{$row}", $subnet->address);
                // Logical Servers
                $txt = '';
                foreach ($lservers as $server) {
                    foreach (explode(', ', $server->address_ip) as $ip) {
                        if ($subnet->contains($ip)) {
                            if (strlen($txt) > 0) {
                                $txt .= ', ';
                            }
                            $txt .= $server->name;
                        }
                    }
                }
                $sheet->setCellValue("F{$row}", $txt);
                // Physical Servers
                $txt = '';
                foreach ($pservers as $server) {
                    foreach (explode(', ', $server->address_ip) as $ip) {
                        if ($subnet->contains($ip)) {
                            if (strlen($txt) > 0) {
                                $txt .= ', ';
                            }
                            $txt .= $server->name;
                        }
                    }
                }
                $sheet->setCellValue("G{$row}", $txt);
                // Switches
                $txt = '';
                foreach ($switches as $switch) {
                    foreach (explode(', ', $switch->ip) as $ip) {
                        if ($subnet->contains($ip)) {
                            if (strlen($txt) > 0) {
                                $txt .= ', ';
                            }
                            $txt .= $switch->name;
                        }
                    }
                }
                $sheet->setCellValue("H{$row}", $txt);
                // Workstations
                $txt = '';
                foreach ($workstations as $workstation) {
                    foreach (explode(', ', $workstation->address_ip) as $ip) {
                        if ($subnet->contains($ip)) {
                            if (strlen($txt) > 0) {
                                $txt .= ', ';
                            }
                            $txt .= $workstation->name;
                        }
                    }
                }
                $sheet->setCellValue("I{$row}", $txt);

                if ($vlan->subnetworks->last() !== $subnet) {
                    $row++;
                }
            }

            // Workstations
            $row++;
        }

        if ($request->get('format') == 'csv') {
            $writer = new Csv($spreadsheet);
            $path = storage_path('app/vlans-'.Carbon::today()->format('Ymd').'.csv');
        } else {
            $writer = new Xlsx($spreadsheet);
            $path = storage_path('app/vlans-'.Carbon::today()->format('Ymd').'.xlsx');
        }

        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
