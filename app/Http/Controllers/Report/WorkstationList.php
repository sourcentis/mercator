<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
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

class WorkstationList extends Controller
{
    /**
     * @throws Exception
     */
    public function generate(Request $request)
    {
        abort_if(Gate::denies('reports_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $workstations = Workstation::All()->sortBy('name');
        $workstations->load('applications', 'site', 'building');

        $header = [
            'Name',
            'Type',
            'Description',
            'OS',
            'IP',
            'Applications',
            'CPU',
            'RAM',
            'Disk',
            'Room',
            'Building',
        ];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$header], null, 'A1');

        // bold title
        $sheet->getStyle('1')->getFont()->setBold(true);

        // Widths
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setWidth(100, 'pt'); // description
        $sheet->getColumnDimension('D')->setAutoSize(true); // OS
        $sheet->getColumnDimension('E')->setAutoSize(true); // IP
        $sheet->getColumnDimension('F')->setWidth(100, 'pt'); // applications
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);

        // converter
        $html = new Html;

        // Populate the Timesheet
        $row = 2;

        // create the sheet
        foreach ($workstations as $ws) {
            $sheet->setCellValue("A{$row}", $ws->name);
            $sheet->setCellValue("B{$row}", $ws->type);
            $sheet->setCellValue("C{$row}", $html->toRichTextObject($ws->description));
            $sheet->setCellValue("D{$row}", $ws->operating_system);
            $sheet->setCellValue("E{$row}", $ws->address_ip);

            $txt = '';
            foreach ($ws->applications as $application) {
                $txt .= $application->name;
                if ($ws->applications->last() !== $application) {
                    $txt .= ', ';
                }
            }
            $sheet->setCellValue("F{$row}", $txt);

            $sheet->setCellValue("G{$row}", $ws->cpu);
            $sheet->setCellValue("H{$row}", $ws->memory);
            $sheet->setCellValue("I{$row}", $ws->disk);
            $sheet->setCellValue("J{$row}", $ws->site ? $ws->site->name : '');
            $sheet->setCellValue("K{$row}", $ws->building ? $ws->building->name : '');

            $row++;
        }

        if ($request->get('format') == 'csv') {
            $writer = new Csv($spreadsheet);
            $path = storage_path('app/workstations-'.Carbon::today()->format('Ymd').'.csv');
        } else {
            $writer = new Xlsx($spreadsheet);
            $path = storage_path('app/workstations-'.Carbon::today()->format('Ymd').'.xlsx');
        }

        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
