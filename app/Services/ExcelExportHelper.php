<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Helper\Html;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class ExcelExportHelper
{
    private Html $html;

    public function __construct()
    {
        $this->html = new Html;
    }

    public function prepareRichText(?string $value): string|RichText
    {
        if (empty($value)) {
            return '';
        }

        // Si le contenu contient du HTML (balises), on utilise toRichTextObject
        if ($value !== strip_tags($value)) {
            return $this->html->toRichTextObject($value);
        }

        // Sinon, on remplace les \n par de vrais sauts de ligne Excel
        // sans passer par du HTML
        return str_replace(["\r\n", "\r"], "\n", $value);
    }
}
