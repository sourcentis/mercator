<?php

namespace App\Http\Controllers\Report;

use App\Models\ApplicationBlock;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Helper\Html;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class ApplicationList extends ReportController
{
    /**
     * @throws Exception
     */
    public function generate(Request $request)
    {
        abort_if(Gate::denies('reports_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationBlocks = ApplicationBlock::All()->sortBy('name');
        $applicationBlocks->load('applications');

        $header = [
            trans('cruds.application.fields.application_block'),
            trans('cruds.application.fields.name'),
            trans('cruds.application.fields.description'),
            'CPE',
            trans('cruds.application.fields.entity_resp'),
            trans('cruds.application.fields.entities'),
            trans('cruds.application.fields.responsible'),
            trans('cruds.application.fields.processes'),
            trans('cruds.application.fields.activities'),
            trans('cruds.application.fields.editor'),
            trans('cruds.application.fields.technology'),
            trans('cruds.application.fields.type'),
            trans('cruds.application.fields.users'),
            trans('cruds.application.fields.external'),
            trans('global.confidentiality_short'),
            trans('global.integrity_short'),
            trans('global.availability_short'),
            trans('global.tracability_short'),
        ];
        if (config('mercator.parameters.security_need_auth')) {
            array_push(
                $header,
                trans('global.authenticity_short')
            );
        }
        array_push(
            $header,
            trans('cruds.application.fields.RTO'),
            trans('cruds.application.fields.RPO'),
            trans('cruds.application.fields.documentation'),
            trans('cruds.application.fields.logical_servers'),
            trans('cruds.physicalServer.title'),
            trans('cruds.workstation.title'),
            trans('cruds.database.title'),
        );

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$header], null, 'A1');

        $i = 0;
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);  // block
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);  // name
        $sheet->getColumnDimension(self::col($i++))->setWidth(60, 'pt'); // description
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);  // CPE
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);  // entity_resp
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);  // entities
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);  // resp
        $sheet->getColumnDimension(self::col($i++))->setWidth(60, 'pt'); // process
        $sheet->getColumnDimension(self::col($i++))->setWidth(60, 'pt'); // activities
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);  // editor
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);  // tech
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);  // type
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);  // users
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);  // external
        // CIAT
        $sheet->getColumnDimension(self::col($i))->setWidth(10, 'pt');
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension(self::col($i))->setWidth(10, 'pt');
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension(self::col($i))->setWidth(10, 'pt');
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension(self::col($i))->setWidth(10, 'pt');
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        if (config('mercator.parameters.security_need_auth')) {
            $sheet->getColumnDimension(self::col($i))->setWidth(10, 'pt');
            $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        // RTO - RPO
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);

        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);
        $sheet->getColumnDimension(self::col($i++))->setWidth(200, 'pt');  // logical servers
        $sheet->getColumnDimension(self::col($i++))->setWidth(200, 'pt');  // physical serveurs
        $sheet->getColumnDimension(self::col($i++))->setWidth(200, 'pt');  // workstations
        $sheet->getColumnDimension(self::col($i++))->setWidth(200, 'pt');  // databases

        // bold title
        $sheet->getStyle('1')->getFont()->setBold(true);

        // converter
        $html = new Html;

        // Populate the sheet
        $row = 2;
        foreach ($applicationBlocks as $applicationBlock) {
            foreach ($applicationBlock->applications as $application) {
                $i = 0;
                $sheet->setCellValue(self::col($i++).$row, $applicationBlock->name);
                $sheet->setCellValue(self::col($i++).$row, $application->name);
                $sheet->setCellValue(self::col($i++).$row, $html->toRichTextObject($application->description));
                $sheet->setCellValue(self::col($i++).$row, $application->vendor.':'.$application->product.':'.$application->version);
                $sheet->setCellValue(self::col($i++).$row, $application->entityResp ? $application->entityResp->name : '');
                $sheet->setCellValue(self::col($i++).$row, $application->entities->implode('name', ', '));
                $sheet->setCellValue(self::col($i++).$row, $application->responsible);
                $sheet->setCellValue(self::col($i++).$row, $application->processes->implode('name', ', '));
                $sheet->setCellValue(self::col($i++).$row, $application->activities->implode('name', ', '));
                $sheet->setCellValue(self::col($i++).$row, $application->editor);
                $sheet->setCellValue(self::col($i++).$row, $application->technology);
                $sheet->setCellValue(self::col($i++).$row, $application->type);
                $sheet->setCellValue(self::col($i++).$row, $application->users);
                $sheet->setCellValue(self::col($i++).$row, $application->external);

                $sheet->setCellValue(self::col($i).$row, $application->security_need_c);
                self::addSecurityNeedColor($sheet, self::col($i++).$row, $application->security_need_c);

                $sheet->setCellValue(self::col($i).$row, $application->security_need_i);
                self::addSecurityNeedColor($sheet, self::col($i++).$row, $application->security_need_i);

                $sheet->setCellValue(self::col($i).$row, $application->security_need_a);
                self::addSecurityNeedColor($sheet, self::col($i++).$row, $application->security_need_a);

                $sheet->setCellValue(self::col($i).$row, $application->security_need_t);
                self::addSecurityNeedColor($sheet, self::col($i++).$row, $application->security_need_t);

                if (config('mercator.parameters.security_need_auth')) {
                    $sheet->setCellValue(self::col($i).$row, $application->security_need_auth);
                    self::addSecurityNeedColor($sheet, self::col($i++).$row, $application->security_need_auth);
                }

                $sheet->setCellValue(self::col($i++).$row, $application->rto);
                $sheet->setCellValue(self::col($i++).$row, $application->rpo);

                $sheet->setCellValue(self::col($i++).$row, $application->documentation);
                $sheet->setCellValue(self::col($i++).$row, $application->logicalServers->implode('name', ', '));
                $res = null;

                // Done: request improved
                $res = DB::Table('physical_servers')
                    ->distinct()
                    ->select('physical_servers.name')
                    ->join(
                        'logical_server_physical_server',
                        'physical_servers.id',
                        '=',
                        'logical_server_physical_server.physical_server_id'
                    )
                    ->join(
                        'logical_servers',
                        'logical_servers.id',
                        '=',
                        'logical_server_physical_server.logical_server_id'
                    )
                    ->join(
                        'application_logical_server',
                        'application_logical_server.logical_server_id',
                        '=',
                        'logical_servers.id'
                    )

                    ->leftJoin(
                        'application_physical_server',
                        'application_physical_server.physical_server_id',
                        '=',
                        'physical_servers.id',
                    )
                    ->whereNull('logical_servers.deleted_at')
                    ->whereNull('physical_servers.deleted_at')
                    ->where('application_logical_server.application_id', '=', $application->id)
                    ->orWhere('application_physical_server.application_id', '=', $application->id)
                    ->orderBy('physical_servers.name')
                    ->get()
                    ->implode('name', ', ');

                $sheet->setCellValue(self::col($i++).$row, $res);
                $sheet->setCellValue(self::col($i++).$row, $application->workstations->implode('name', ', '));
                $sheet->setCellValue(self::col($i++).$row, $application->databases->implode('name', ', '));

                $row++;
            }
        }

        if ($request->get('format') == 'csv') {
            $writer = new Csv($spreadsheet);
            $path = storage_path('app/applications-'.Carbon::today()->format('Ymd').'.csv');
        } else {
            $writer = new Xlsx($spreadsheet);
            $path = storage_path('app/applications-'.Carbon::today()->format('Ymd').'.xlsx');
        }

        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
