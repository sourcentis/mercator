<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\LogicalServer;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Helper\Html;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class LogicalServerConfigs extends Controller
{
    /**
     * @throws Exception
     */
    public function generate(Request $request)
    {
        abort_if(Gate::denies('reports_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logicalServers = LogicalServer::All()->sortBy('name');
        $logicalServers->load('applications', 'physicalServers');

        $header = [
            trans('cruds.logicalServer.fields.name'),               // A
            trans('cruds.logicalServer.fields.operating_system'),   // B
            trans('cruds.logicalServer.fields.environment'),        // C
            trans('cruds.logicalServer.fields.install_date'),       // D
            trans('cruds.logicalServer.fields.update_date'),        // E
            trans('cruds.logicalServer.fields.cpu'),                // F
            trans('cruds.logicalServer.fields.memory'),             // G
            trans('cruds.logicalServer.fields.disk'),               // H
            trans('cruds.logicalServer.fields.net_services'),       // I
            trans('cruds.logicalServer.fields.address_ip'),         // J
            trans('cruds.logicalServer.fields.configuration'),      // K
            trans('cruds.logicalServer.fields.applications'),       // L
            trans('cruds.application.fields.application_block'),    // M
            trans('cruds.logicalServer.fields.cluster'),            // N
            trans('cruds.logicalServer.fields.servers'),            // O
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
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);
        $sheet->getColumnDimension('M')->setAutoSize(true);
        $sheet->getColumnDimension('N')->setAutoSize(true);
        $sheet->getColumnDimension('O')->setAutoSize(true);

        // center (CPU, Men, Disk)
        $sheet->getStyle('F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // converter
        $html = new Html;

        // Populate the Timesheet
        $row = 2;
        foreach ($logicalServers as $logicalServer) {
            $sheet->setCellValue("A{$row}", $logicalServer->name);
            $sheet->setCellValue("B{$row}", $logicalServer->operating_system);
            $sheet->setCellValue("C{$row}", $logicalServer->environment);
            $sheet->setCellValue("D{$row}", $logicalServer->install_date);
            $sheet->setCellValue("E{$row}", $logicalServer->update_date);
            $sheet->setCellValue("F{$row}", $logicalServer->cpu);
            $sheet->setCellValue("G{$row}", $logicalServer->memory);
            $sheet->setCellValue("H{$row}", $logicalServer->disk);
            $sheet->setCellValue("I{$row}", $logicalServer->net_services);
            $sheet->setCellValue("J{$row}", $logicalServer->address_ip);
            $sheet->setCellValue("K{$row}", $html->toRichTextObject($logicalServer->configuration));
            $sheet->setCellValue("L{$row}", $logicalServer->applications->implode('name', ', '));
            $sheet->setCellValue("M{$row}", $logicalServer->applications->first() !== null ?
                ($logicalServer->applications->first()->application_block_id !== null ? $logicalServer->applications->first()->applicationBlock->name : '') : '');
            $sheet->setCellValue("N{$row}", $logicalServer->cluster->name ?? '');
            $sheet->setCellValue("O{$row}", $logicalServer->physicalServers->implode('name', ', '));

            $row++;
        }

        if ($request->get('format') == 'csv') {
            $writer = new Csv($spreadsheet);
            $path = storage_path('app/logicalServers-'.Carbon::today()->format('Ymd').'.csv');
        } else {
            $writer = new Xlsx($spreadsheet);
            $path = storage_path('app/logicalServers-'.Carbon::today()->format('Ymd').'.xlsx');
        }

        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
