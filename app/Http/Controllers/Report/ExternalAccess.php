<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\ExternalConnectedEntity;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Helper\Html;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class ExternalAccess extends Controller
{
    /**
     * @throws Exception
     */
    public function generate(Request $request)
    {
        abort_if(Gate::denies('reports_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $accesses = ExternalConnectedEntity::All()->sortBy('name');
        $accesses->load('entity', 'network');

        $header = [
            trans('cruds.externalConnectedEntity.fields.name'),
            trans('cruds.externalConnectedEntity.fields.type'),
            trans('cruds.entity.fields.name'),
            trans('cruds.entity.fields.description'),
            trans('cruds.entity.fields.contact_point'),
            trans('cruds.externalConnectedEntity.fields.description'),
            trans('cruds.externalConnectedEntity.fields.contacts'),
            trans('cruds.externalConnectedEntity.fields.network'),
            trans('cruds.externalConnectedEntity.fields.src'),
            trans('cruds.externalConnectedEntity.fields.src'),
            trans('cruds.externalConnectedEntity.fields.dest'),
            trans('cruds.externalConnectedEntity.fields.dest'),
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
        $sheet->getColumnDimension('D')->setWidth(150, 'pt'); // description
        $sheet->getColumnDimension('E')->setWidth(150, 'pt'); // contact point
        $sheet->getColumnDimension('F')->setWidth(150, 'pt'); // reason
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);

        // converter
        $html = new Html;

        // Populate the Timesheet
        $row = 2;
        foreach ($accesses as $access) {
            $sheet->setCellValue("A{$row}", $access->name);
            $sheet->setCellValue("B{$row}", $access->type);
            $sheet->setCellValue("C{$row}", $access->entity ? $access->entity->name : '');
            $sheet->setCellValue("D{$row}", $access->entity ? $html->toRichTextObject($access->entity->description) : '');
            $sheet->setCellValue("E{$row}", $access->entity ? $html->toRichTextObject($access->entity->contact_point) : '');
            $sheet->setCellValue("F{$row}", $html->toRichTextObject($access->description));
            $sheet->setCellValue("G{$row}", $access->contacts);
            $sheet->setCellValue("H{$row}", $access->network ? $access->network->name : '');
            $sheet->setCellValue("I{$row}", $access->src_desc);
            $sheet->setCellValue("J{$row}", $access->src);
            $sheet->setCellValue("K{$row}", $access->dest_desc);
            $sheet->setCellValue("L{$row}", $access->dest);

            $row++;
        }

        if ($request->get('format') == 'csv') {
            $writer = new Csv($spreadsheet);
            $path = storage_path('app/externalAccess-'.Carbon::today()->format('Ymd').'.csv');
        } else {
            $writer = new Xlsx($spreadsheet);
            $path = storage_path('app/externalAccess-'.Carbon::today()->format('Ymd').'.xlsx');
        }

        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
