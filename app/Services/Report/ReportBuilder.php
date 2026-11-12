<?php

namespace App\Services\Report;

use App\Support\ReportTemplateSettings;
use Carbon\Carbon;

class ReportBuilder
{
    /**
     * Vue ids in document rendering order (GDPR first, per user decision).
     *
     * @var array<int, string>
     */
    private const VUE_ORDER = ['7', '1', '2', '3', '4', '5', '6'];

    /**
     * @var array<int, class-string<ReportSection>>
     */
    private const VUE_SECTION_CLASSES = [
        1 => EcosystemSection::class,
        2 => InformationSystemSection::class,
        3 => ApplicationSection::class,
        4 => AdministrationSection::class,
        5 => LogicalInfrastructureSection::class,
        6 => PhysicalInfrastructureSection::class,
        7 => GdprSection::class,
    ];

    public function __construct(
        private readonly WordHelper $wordHelper = new WordHelper,
        private readonly TemplateMergerService $templateMerger = new TemplateMergerService
    ) {}

    /**
     * @param  array<int, string>  $selectedVues
     */
    public function build(array $selectedVues, bool $withGraphs = false): string
    {
        $vues = $this->normalizeSelectedVues($selectedVues);
        $this->wordHelper->setIncludeGraphs($withGraphs);

        $phpWord = $this->wordHelper->newDocument();
        $section = $phpWord->addSection();

        foreach (self::VUE_ORDER as $vueId) {
            if (! in_array($vueId, $vues, true)) {
                continue;
            }

            $sectionClass = self::VUE_SECTION_CLASSES[$vueId];
            (new $sectionClass)->build($section, $this->wordHelper, $vues);

            $section->addPageBreak();
        }

        $reportsDirectory = storage_path('app/reports');
        if (! is_dir($reportsDirectory)) {
            mkdir($reportsDirectory, 0755, true);
        }

        $bodyPath = storage_path('app/reports/body-'.uniqid().'.docx');

        try {
            WordHelper::saveDocx($phpWord, $bodyPath);
        } finally {
            $this->wordHelper->cleanupTempFiles();
        }

        $filepath = storage_path('app/reports/cartographie-'.Carbon::today()->format('Ymd').'-'.uniqid().'.docx');

        try {
            $this->templateMerger->merge(ReportTemplateSettings::currentTemplatePath(), $bodyPath, $filepath);
        } finally {
            @unlink($bodyPath);
        }

        return $filepath;
    }

    /**
     * @param  array<int, string>  $selectedVues
     * @return array<int, string>
     */
    private function normalizeSelectedVues(array $selectedVues): array
    {
        if (count($selectedVues) === 0) {
            return self::VUE_ORDER;
        }

        return $selectedVues;
    }
}
