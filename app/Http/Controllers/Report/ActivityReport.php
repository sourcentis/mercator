<?php

namespace App\Http\Controllers\Report;

// GDPR
// ecosystem
use App\Models\DataProcessing;
use App\Services\Report\WordHelper;
use Carbon\Carbon;
use Gate;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;
use Symfony\Component\HttpFoundation\Response;

// information system
// Application
// Administration
// Logique
// Physique
// Laravel
// PhpOffice
// see : https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/

class ActivityReport extends ReportController
{
    public function generateDocx()
    {
        abort_if(Gate::denies('reports_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // get template
        $phpWord = new PhpWord;
        Settings::setOutputEscapingEnabled(true);
        $phpWord->getSettings()->setHideGrammaticalErrors(true);
        $phpWord->getSettings()->setHideSpellingErrors(true);
        $section = $phpWord->addSection();

        // Numbering Style
        $phpWord->addNumberingStyle(
            'hNum',
            ['type' => 'multilevel', 'levels' => [
                ['pStyle' => 'Heading1', 'format' => 'decimal', 'text' => '%1.'],
                ['pStyle' => 'Heading2', 'format' => 'decimal', 'text' => '%1.%2.'],
                ['pStyle' => 'Heading3', 'format' => 'decimal', 'text' => '%1.%2.%3.'],
            ],
            ]
        );
        $phpWord->addTitleStyle(
            0,
            ['size' => 28, 'bold' => true],
            ['align' => 'center']
        );
        $phpWord->addTitleStyle(
            1,
            ['size' => 16, 'bold' => true],
            ['spaceAfter' => 100, 'spaceBefore' => 100, 'numStyle' => 'hNum', 'numLevel' => 0]
        );
        $phpWord->addTitleStyle(
            2,
            ['size' => 14, 'bold' => true],
            ['spaceAfter' => 100, 'spaceBefore' => 100, 'numStyle' => 'hNum', 'numLevel' => 1]
        );
        $phpWord->addTitleStyle(
            3,
            ['size' => 12, 'bold' => true],
            ['numStyle' => 'hNum', 'numLevel' => 2]
        );

        // Title
        $section->addTitle(trans('cruds.dataProcessing.report_title'), 0);
        $section->addTextBreak(1);

        // TOC
        $toc = $section->addTOC(['spaceAfter' => 50, 'size' => 10]);
        $toc->setMinDepth(1);
        $toc->setMaxDepth(1);
        $section->addTextBreak(1);

        // page break
        $section->addPageBreak();

        // Add footer
        $footer = $section->addFooter();
        $footer->addPreserveText('{PAGE} / {NUMPAGES}', ['size' => 8], ['alignment' => Jc::CENTER]);

        $register = DataProcessing::query()
            ->orderBy('name')
            ->get();
        foreach ($register as $dataProcessing) {
            // schema
            $section->addTitle($dataProcessing->name, 1);

            $section->addTitle(trans('cruds.dataProcessing.fields.legal_basis'), 2);
            self::addText($section, $dataProcessing->legal_basis);

            $section->addTitle(trans('cruds.dataProcessing.fields.description'), 2);
            self::addText($section, $dataProcessing->description);

            $section->addTitle(trans('cruds.dataProcessing.fields.responsible'), 2);
            self::addText($section, $dataProcessing->responsible);

            $section->addTitle(trans('cruds.dataProcessing.fields.purpose'), 2);
            self::addText($section, $dataProcessing->purpose);

            $section->addTitle(trans('cruds.dataProcessing.fields.lawfulness'), 2);

            // Booleans
            $txt = '<ul>';
            if ($dataProcessing->lawfulness_consent) {
                $txt .= '<li>'.trans('cruds.dataProcessing.fields.lawfulness_consent').'</li>';
            }
            if ($dataProcessing->lawfulness_contract) {
                $txt .= '<li>'.trans('cruds.dataProcessing.fields.lawfulness_contract').'</li>';
            }
            if ($dataProcessing->lawfulness_legal_obligation) {
                $txt .= '<li>'.trans('cruds.dataProcessing.fields.lawfulness_legal_obligation').'</li>';
            }
            $txt .= '</ul><br/>';

            $txt .= $dataProcessing->lawfulness;

            self::addText($section, $txt);

            $section->addTitle(trans('cruds.dataProcessing.fields.categories'), 2);
            self::addText($section, $dataProcessing->categories);

            $section->addTitle(trans('cruds.dataProcessing.fields.data_source'), 2);
            self::addText($section, $dataProcessing->data_source);

            $section->addTitle(trans('cruds.dataProcessing.fields.data_collection_obligation'), 2);
            self::addText($section, $dataProcessing->data_collection_obligation);

            $section->addTitle(trans('cruds.dataProcessing.fields.recipients'), 2);
            self::addText($section, $dataProcessing->recipients);

            $section->addTitle(trans('cruds.dataProcessing.fields.transfert'), 2);
            self::addText($section, $dataProcessing->transfert);

            $section->addTitle(trans('cruds.dataProcessing.fields.automated_decision_making'), 2);
            self::addText($section, $dataProcessing->automated_decision_making);

            $section->addTitle(trans('cruds.dataProcessing.fields.retention'), 2);
            self::addText($section, $dataProcessing->retention);

            $section->addTitle(trans('cruds.dataProcessing.fields.data_subject_rights'), 2);
            self::addText($section, $dataProcessing->data_subject_rights);

            $section->addTitle(trans('cruds.dataProcessing.fields.update_date'), 2);
            self::addText($section, $dataProcessing->update_date?->format('d-m-Y'));

            // Processes
            $section->addTitle(trans('cruds.dataProcessing.fields.processes'), 2);
            $txt = '<ul>';
            foreach ($dataProcessing->processes as $p) {
                $txt .= '<li>'.$p->name.'</li>';
            }
            $txt .= '</ul>';
            self::addText($section, $txt);

            // Applications
            $section->addTitle(trans('cruds.dataProcessing.fields.applications'), 2);
            $txt = '<ul>';
            foreach ($dataProcessing->applications as $ap) {
                $txt .= '<li>'.$ap->name.'</li>';
            }
            $txt .= '</ul>';
            self::addText($section, $txt);

            // Informations
            $section->addTitle(trans('cruds.dataProcessing.fields.information'), 2);
            $txt = '<ul>';
            foreach ($dataProcessing->informations as $inf) {
                $txt .= '<li>'.$inf->name.'</li>';
            }
            $txt .= '</ul>';
            self::addText($section, $txt);

            // Security Controls
            $section->addTitle(trans('cruds.dataProcessing.fields.security_controls'), 2);
            // TODO : improve me
            $allControls = Collect();
            foreach ($dataProcessing->processes as $process) {
                foreach ($process->securityControls as $sc) {
                    $allControls->push($sc->name);
                }
            }
            foreach ($dataProcessing->applications as $app) {
                foreach ($app->securityControls as $sc) {
                    $allControls->push($sc->name);
                }
            }
            $allControls->unique();
            $txt = '<ul>';
            foreach ($allControls as $control) {
                $txt .= '<li>'.$control.'</li>';
            }
            $txt .= '</ul>';
            self::addText($section, $txt);
        }

        // Filename
        // uniqid() : deux exports simultanés ne doivent pas écrire (ni supprimer) le même fichier
        $filepath = storage_path('app/reports/register-'.Carbon::today()->format('Ymd').'-'.uniqid().'.docx');

        // Saving the document as Word2007 file.
        WordHelper::saveDocx($phpWord, $filepath);

        // return
        return response()
            ->download($filepath, 'register-'.Carbon::today()->format('Ymd').'.docx')
            ->deleteFileAfterSend(true);
    }
}
