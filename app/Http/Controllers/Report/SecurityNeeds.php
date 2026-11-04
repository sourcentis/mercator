<?php

namespace App\Http\Controllers\Report;

use App\Models\Application;
use App\Models\Database;
use App\Models\Information;
use App\Models\MacroProcessus;
use App\Models\Process;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class SecurityNeeds extends ReportController
{
    /**
     * @throws Exception
     */
    public function generate(Request $request)
    {
        abort_if(Gate::denies('reports_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // macroprocess - process - application - base de données - information
        $header = [];
        array_push(
            $header,
            trans('cruds.macroProcessus.title'),
            trans('global.confidentiality_short'),
            trans('global.integrity_short'),
            trans('global.availability_short'),
            trans('global.tracability_short')
        );
        if (config('mercator.parameters.security_need_auth')) {
            $header[] = trans('global.authenticity_short');
        }
        array_push(
            $header,
            trans('cruds.process.title'),
            trans('global.confidentiality_short'),
            trans('global.integrity_short'),
            trans('global.availability_short'),
            trans('global.tracability_short')
        );
        if (config('mercator.parameters.security_need_auth')) {
            $header[] = trans('global.authenticity_short');
        }
        array_push(
            $header,
            trans('cruds.application.title'),
            trans('global.confidentiality_short'),
            trans('global.integrity_short'),
            trans('global.availability_short'),
            trans('global.tracability_short')
        );
        if (config('mercator.parameters.security_need_auth')) {
            $header[] = trans('global.authenticity_short');
        }
        array_push(
            $header,
            trans('cruds.database.title'),
            trans('global.confidentiality_short'),
            trans('global.integrity_short'),
            trans('global.availability_short'),
            trans('global.tracability_short')
        );
        if (config('mercator.parameters.security_need_auth')) {
            $header[] = trans('global.authenticity_short');
        }
        array_push(
            $header,
            trans('cruds.information.title'),
            trans('global.confidentiality_short'),
            trans('global.integrity_short'),
            trans('global.availability_short'),
            trans('global.tracability_short')
        );
        if (config('mercator.parameters.security_need_auth')) {
            $header[] = trans('global.authenticity_short');
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$header], null, 'A1');

        // Widths
        $i = 0;
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        if (config('mercator.parameters.security_need_auth')) {
            $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        }
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        if (config('mercator.parameters.security_need_auth')) {
            $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        }
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        if (config('mercator.parameters.security_need_auth')) {
            $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        }
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        if (config('mercator.parameters.security_need_auth')) {
            $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        }
        $sheet->getColumnDimension(self::col($i++))->setAutoSize(true);
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        if (config('mercator.parameters.security_need_auth')) {
            $sheet->getColumnDimension(self::col($i++))->setWidth(12, 'pt');
        }

        // Center
        $i = 1;
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        if (config('mercator.parameters.security_need_auth')) {
            $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $i++;
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        if (config('mercator.parameters.security_need_auth')) {
            $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $i++;
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        if (config('mercator.parameters.security_need_auth')) {
            $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $i++;
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        if (config('mercator.parameters.security_need_auth')) {
            $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $i++;
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        if (config('mercator.parameters.security_need_auth')) {
            $sheet->getStyle(self::col($i++))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // bold title
        $sheet->getStyle('1')->getFont()->setBold(true);

        // Populate the Timesheet
        $row = 2;

        // loop
        $macroprocesses = MacroProcessus::with('processes')->get();
        foreach ($macroprocesses as $macroprocess) {
            if ($macroprocess->processes->count() === 0) {
                $this->addLine($sheet, $row, $macroprocess, null, null, null, null);
                $row++;
            } else {
                foreach ($macroprocess->processes as $process) {
                    if ($process->applications->count() === 0) {
                        $this->addLine($sheet, $row, $macroprocess, $process, null, null, null);
                        $row++;
                    } else {
                        foreach ($process->applications as $application) {
                            if ($application->databases->count() === 0) {
                                $this->addLine($sheet, $row, $macroprocess, $process, $application, null, null);
                                $row++;
                            } else {
                                foreach ($application->databases as $database) {
                                    if ($database->informations->count() === 0) {
                                        $this->addLine($sheet, $row, $macroprocess, $process, $application, $database, null);
                                        $row++;
                                    } else {
                                        foreach ($database->informations as $information) {
                                            $this->addLine($sheet, $row, $macroprocess, $process, $application, $database, $information);
                                            $row++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($request->has('format') == 'csv') {
            $writer = new Csv($spreadsheet);
            $path = storage_path('app/securityNeeds-'.Carbon::today()->format('Ymd').'.csv');
        } else {
            $writer = new Xlsx($spreadsheet);
            $path = storage_path('app/securityNeeds-'.Carbon::today()->format('Ymd').'.xlsx');
        }

        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    protected static function addLine(
        Worksheet $sheet,
        int $row,
        MacroProcessus $macroprocess,
        ?Process $process = null,
        ?Application $application = null,
        ?Database $database = null,
        ?Information $information = null
    ): void {
        // Macroprocessus
        $i = 0;
        $sheet->setCellValue(self::col($i++).$row, $macroprocess->name);

        $sheet->setCellValue(self::col($i).$row, $macroprocess->security_need_c >= 0 ? $macroprocess->security_need_c : '');
        self::addSecurityNeedColor($sheet, self::col($i++).$row, $macroprocess->security_need_c);

        $sheet->setCellValue(self::col($i).$row, $macroprocess->security_need_i >= 0 ? $macroprocess->security_need_i : '');
        self::addSecurityNeedColor($sheet, self::col($i++).$row, $macroprocess->security_need_i);

        $sheet->setCellValue(self::col($i).$row, $macroprocess->security_need_a >= 0 ? $macroprocess->security_need_a : '');
        self::addSecurityNeedColor($sheet, self::col($i++).$row, $macroprocess->security_need_a);

        $sheet->setCellValue(self::col($i).$row, $macroprocess->security_need_t >= 0 ? $macroprocess->security_need_t : '');
        self::addSecurityNeedColor($sheet, self::col($i++).$row, $macroprocess->security_need_t);

        if (config('mercator.parameters.security_need_auth')) {
            $sheet->setCellValue(self::col($i).$row, $macroprocess->security_need_auth >= 0 ? $macroprocess->security_need_auth : '');
            self::addSecurityNeedColor($sheet, self::col($i++).$row, $macroprocess->security_need_auth);
        }

        if ($process !== null) {
            // Processus
            $sheet->setCellValue(self::col($i++).$row, $process->name);

            $sheet->setCellValue(self::col($i).$row, $process->security_need_c >= 0 ? $process->security_need_c : '');
            self::addSecurityNeedColor($sheet, self::col($i++).$row, $process->security_need_c);

            $sheet->setCellValue(self::col($i).$row, $process->security_need_i >= 0 ? $process->security_need_i : '');
            self::addSecurityNeedColor($sheet, self::col($i++).$row, $process->security_need_i);

            $sheet->setCellValue(self::col($i).$row, $process->security_need_a >= 0 ? $process->security_need_a : '');
            self::addSecurityNeedColor($sheet, self::col($i++).$row, $process->security_need_a);

            $sheet->setCellValue(self::col($i).$row, $process->security_need_t >= 0 ? $process->security_need_t : '');
            self::addSecurityNeedColor($sheet, self::col($i++).$row, $process->security_need_t);

            if (config('mercator.parameters.security_need_auth')) {
                $sheet->setCellValue(self::col($i).$row, $process->security_need_auth >= 0 ? $process->security_need_auth : '');
                self::addSecurityNeedColor($sheet, self::col($i++).$row, $process->security_need_auth);
            }

            if ($application !== null) {
                // Application
                $sheet->setCellValue(self::col($i++).$row, $application->name);

                $sheet->setCellValue(self::col($i).$row, $application->security_need_c >= 0 ? $application->security_need_c : '');
                self::addSecurityNeedColor($sheet, self::col($i++).$row, $application->security_need_c);

                $sheet->setCellValue(self::col($i).$row, $application->security_need_i >= 0 ? $application->security_need_i : '');
                self::addSecurityNeedColor($sheet, self::col($i++).$row, $application->security_need_i);

                $sheet->setCellValue(self::col($i).$row, $application->security_need_a >= 0 ? $application->security_need_a : '');
                self::addSecurityNeedColor($sheet, self::col($i++).$row, $application->security_need_a);

                $sheet->setCellValue(self::col($i).$row, $application->security_need_t >= 0 ? $application->security_need_t : '');
                self::addSecurityNeedColor($sheet, self::col($i++).$row, $application->security_need_t);

                if (config('mercator.parameters.security_need_auth')) {
                    $sheet->setCellValue(self::col($i).$row, $application->security_need_auth >= 0 ? $application->security_need_auth : '');
                    self::addSecurityNeedColor($sheet, self::col($i++).$row, $application->security_need_auth);
                }

                if ($database !== null) {
                    // Database
                    $sheet->setCellValue(self::col($i++).$row, $database->name);

                    $sheet->setCellValue(self::col($i).$row, $database->security_need_c >= 0 ? $database->security_need_c : '');
                    self::addSecurityNeedColor($sheet, self::col($i++).$row, $database->security_need_c);

                    $sheet->setCellValue(self::col($i).$row, $database->security_need_i >= 0 ? $database->security_need_i : '');
                    self::addSecurityNeedColor($sheet, self::col($i++).$row, $database->security_need_i);

                    $sheet->setCellValue(self::col($i).$row, $database->security_need_a >= 0 ? $database->security_need_a : '');
                    self::addSecurityNeedColor($sheet, self::col($i++).$row, $database->security_need_a);

                    $sheet->setCellValue(self::col($i).$row, $database->security_need_t >= 0 ? $database->security_need_t : '');
                    self::addSecurityNeedColor($sheet, self::col($i++).$row, $database->security_need_t);

                    if (config('mercator.parameters.security_need_auth')) {
                        $sheet->setCellValue(self::col($i).$row, $database->security_need_auth >= 0 ? $database->security_need_auth : '');
                        self::addSecurityNeedColor($sheet, self::col($i++).$row, $database->security_need_auth);
                    }

                    if ($information !== null) {
                        // Information
                        $sheet->setCellValue(self::col($i++).$row, $information->name);

                        $sheet->setCellValue(self::col($i).$row, $information->security_need_c >= 0 ? $information->security_need_c : '');
                        self::addSecurityNeedColor($sheet, self::col($i++).$row, $information->security_need_c);

                        $sheet->setCellValue(self::col($i).$row, $information->security_need_i >= 0 ? $information->security_need_i : '');
                        self::addSecurityNeedColor($sheet, self::col($i++).$row, $information->security_need_i);

                        $sheet->setCellValue(self::col($i).$row, $information->security_need_a >= 0 ? $information->security_need_a : '');
                        self::addSecurityNeedColor($sheet, self::col($i++).$row, $information->security_need_a);

                        $sheet->setCellValue(self::col($i).$row, $information->security_need_t >= 0 ? $information->security_need_t : '');
                        self::addSecurityNeedColor($sheet, self::col($i++).$row, $information->security_need_t);

                        if (config('mercator.parameters.security_need_auth')) {
                            $sheet->setCellValue(self::col($i).$row, $information->security_need_auth >= 0 ? $information->security_need_auth : '');
                            self::addSecurityNeedColor($sheet, self::col($i++).$row, $information->security_need_auth);
                        }
                    }
                }
            }
        }
    }
}
