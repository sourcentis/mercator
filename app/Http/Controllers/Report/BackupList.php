<?php

namespace App\Http\Controllers\Report;

use App\Models\Backup;
use Gate;
use PhpOffice\PhpSpreadsheet\Helper\Html;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class BackupList extends ReportController
{
    /**
     * @throws Exception
     */
    public function generateExcel(): Response
    {
        abort_if(Gate::denies('reports_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $backups = Backup::query()
            ->with('logicalServers', 'storageDevices')
            ->get()
            ->sortBy('name');

        $header = [
            trans('cruds.backup.fields.name'),
            trans('cruds.logicalServer.title_short'),
            trans('cruds.storageDevice.title_short'),
            trans('cruds.backup.frequency'),
            trans('cruds.backup.cycle'),
            trans('cruds.backup.retention'),
        ];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$header], null, 'A1');

        $sheet->getStyle('1')->getFont()->setBold(true);
        $sheet->getDefaultRowDimension()->setRowHeight(-1);

        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $html = new Html;

        $row = 2;
        foreach ($backups as $backup) {
            $servers = $backup->logicalServers->pluck('name')->implode(', ');
            $devices = $backup->storageDevices->pluck('name')->implode(', ');

            $sheet->setCellValue("A{$row}", $backup->name);
            $sheet->setCellValue("B{$row}", $servers);
            $sheet->setCellValue("C{$row}", $devices);
            $sheet->setCellValue("D{$row}", $backup->backup_frequency ? trans("cruds.backup.frequencies.{$backup->backup_frequency}") : '');
            $sheet->setCellValue("E{$row}", $backup->backup_cycle ? trans("cruds.backup.cycles.{$backup->backup_cycle}") : '');
            $sheet->setCellValue("F{$row}", $backup->backup_retention);

            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $path = storage_path('app/backups-'.now()->format('Ymd').'.xlsx');
        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
