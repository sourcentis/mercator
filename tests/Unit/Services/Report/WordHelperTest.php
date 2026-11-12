<?php

use App\Models\DataProcessing;
use App\Models\Entity;
use App\Models\Man;
use App\Models\PhysicalLink;
use App\Models\Relation;
use App\Models\SecurityControl;
use App\Services\Report\WordHelper;
use PhpOffice\PhpWord\Settings;

it('MODEL_VUE_MAP preserves every model -> vue id mapping unchanged', function () {
    $map = WordHelper::MODEL_VUE_MAP;

    expect($map)->toHaveCount(55);
    expect($map[Entity::class])->toBe('1');
    expect($map[Relation::class])->toBe('1');
    expect($map[DataProcessing::class])->toBe('7');
    expect($map[SecurityControl::class])->toBe('7');
    expect($map[Man::class])->toBe('6');
    expect($map[PhysicalLink::class])->toBe('6');
});

/**
 * @return mixed
 */
function callWordHelperPrivateMethod(WordHelper $helper, string $method, array $args = [])
{
    $reflection = new ReflectionMethod($helper, $method);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs($helper, $args);
}

describe('WordHelper::countGraphNodes (private, via reflection)', function () {
    test('counts a single node declaration', function () {
        $dot = "digraph  {\nA [label=\"A\" shape=none]\n}";

        expect(callWordHelperPrivateMethod(new WordHelper, 'countGraphNodes', [$dot]))->toBe(1);
    });

    test('counts two nodes and ignores the edge between them', function () {
        $dot = "digraph  {\nA [label=\"A\"]\nB [label=\"B\"]\nA -> B\n}";

        expect(callWordHelperPrivateMethod(new WordHelper, 'countGraphNodes', [$dot]))->toBe(2);
    });

    test('ignores graph/node/edge default-attribute statements', function () {
        $dot = "digraph {\ngraph [rankdir=TB fontname=\"FreeSans\"]\nnode  [fontname=\"FreeSans\"]\nedge  [fontname=\"FreeSans\"]\nZONE1 [label=\"Z\"]\n}";

        expect(callWordHelperPrivateMethod(new WordHelper, 'countGraphNodes', [$dot]))->toBe(1);
    });
});

describe('WordHelper::applyGraphDefaults (private, via reflection)', function () {
    test('injects graph/node/edge fontsize 1pt below the document default, before any node', function () {
        $dot = "digraph  {\nA [label=\"A\"]\n}";
        $expectedSize = Settings::getDefaultFontSize() - 1;

        $result = callWordHelperPrivateMethod(new WordHelper, 'applyGraphDefaults', [$dot]);

        expect($result)
            ->toContain('graph [fontsize='.$expectedSize.']')
            ->toContain('node [fontsize='.$expectedSize.']')
            ->toContain('edge [fontsize='.$expectedSize.']');
        expect(mb_strpos($result, 'fontsize'))->toBeLessThan(mb_strpos($result, 'A [label'));
    });
});

describe('WordHelper::capWidthToPt (private static, via reflection — the pure px/dpi -> pt calculation)', function () {
    test('converts pixels to points at the given DPI, scaled down by NARROW_GRAPH_SCALE', function () {
        // 900px at 300 DPI = 216pt naturally, well under the 450pt cap, then scaled down by
        // NARROW_GRAPH_SCALE (52/72): 216 * 52/72 = 156pt.
        expect(callWordHelperPrivateMethod(new WordHelper, 'capWidthToPt', [900, 300]))->toBe(156.0);
    });

    test('caps a wide graph at 450pt regardless of the narrow-graph scale-down', function () {
        // 3000px at 300 DPI = 720pt naturally; even scaled down (720 * 52/72 = 520pt), it's still
        // above the cap, so the graph still renders at exactly 450pt — full-width graphs are
        // unaffected by the narrow-graph scale-down.
        expect(callWordHelperPrivateMethod(new WordHelper, 'capWidthToPt', [3000, 300]))->toBe(450.0);
    });

    test('never enlarges a graph narrower than 450pt', function () {
        // 300px at 300 DPI = 72pt naturally, scaled down to 52pt (72 * 52/72): must stay 52pt, not
        // be scaled up to 450pt.
        expect(callWordHelperPrivateMethod(new WordHelper, 'capWidthToPt', [300, 300]))->toBe(52.0);
    });
});

describe('WordHelper::insertGraph end-to-end (real dot rasterization)', function () {
    /**
     * @return array{0: string, 1: array<int, int>} [document.xml, media PNG sizes]
     */
    function renderGraphIntoDocx(string $dot): array
    {
        $helper = new WordHelper;
        $phpWord = $helper->newDocument();
        $section = $phpWord->addSection();

        $helper->insertGraph($section, $dot);

        $path = tempnam(sys_get_temp_dir(), 'docx');
        WordHelper::saveDocx($phpWord, $path);
        $helper->cleanupTempFiles();

        $zip = new ZipArchive;
        $zip->open($path);
        $xml = $zip->getFromName('word/document.xml');
        $mediaSizes = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name !== false && str_starts_with($name, 'word/media/') && str_ends_with($name, '.png')) {
                $mediaSizes[] = $zip->statIndex($i)['size'];
            }
        }
        $zip->close();
        unlink($path);

        return [$xml, $mediaSizes];
    }

    test('does not insert a graph with a single node', function () {
        $dot = "digraph  {\nA [label=\"Solo\" shape=none image=\"/nonexistent.png\"]\n}";

        [$xml, $mediaSizes] = renderGraphIntoDocx($dot);

        expect($xml)->not->toContain('<w:pict');
        expect($mediaSizes)->toHaveCount(0);
    });

    test('adds space above and below the graph, since PhpWord\'s Image/Frame style has no spacing of its own', function () {
        $dot = "digraph  {\nA [label=\"A\" shape=box]\nB [label=\"B\" shape=box]\nA -> B\n}";

        [$xml, $mediaSizes] = renderGraphIntoDocx($dot);

        expect($mediaSizes)->toHaveCount(1);
        // The graph is wrapped in its own paragraph (a TextRun) carrying the spacing — confirm the
        // <w:pict> sits inside a <w:spacing before="120" after="120"> paragraph, not a bare one.
        preg_match('/<w:pPr>(.*?)<\/w:pPr>.*?<w:pict/s', $xml, $matches);
        expect($matches[1] ?? '')->toContain('<w:spacing w:before="120" w:after="120"/>');
    });

    test('caps a wide multi-node graph at exactly 450pt while preserving aspect ratio', function () {
        // A long left-to-right chain forces Graphviz to lay the nodes out horizontally, producing
        // an image reliably wider than the 450pt cap.
        $nodes = [];
        $edges = [];
        for ($i = 0; $i < 30; $i++) {
            $nodes[] = 'N'.$i.' [label="Node number '.$i.'" shape=box width=1.5]';
            if ($i > 0) {
                $edges[] = 'N'.($i - 1).' -> N'.$i;
            }
        }
        $dot = "digraph  {\nrankdir=LR;\n".implode("\n", $nodes)."\n".implode("\n", $edges)."\n}";

        [$xml, $mediaSizes] = renderGraphIntoDocx($dot);

        expect($mediaSizes)->toHaveCount(1);
        preg_match('/width:([0-9.]+)pt/', $xml, $matches);
        expect((float) $matches[1])->toBe(450.0);
    });
});

describe('WordHelper::addDescriptionCellWithIcon', function () {
    /**
     * @return array{0: string, 1: array<int, int>} [document.xml, media PNG sizes]
     */
    function renderDescriptionCellIntoDocx(?string $description, Entity $entity, string $fallbackIcon): array
    {
        $helper = new WordHelper;
        $phpWord = $helper->newDocument();
        $section = $phpWord->addSection();
        $table = $helper->addTable($section, 'Test');

        $helper->addDescriptionCellWithIcon($table, 'Description', $description, $entity, $fallbackIcon);

        $path = tempnam(sys_get_temp_dir(), 'docx');
        WordHelper::saveDocx($phpWord, $path);
        $helper->cleanupTempFiles();

        $zip = new ZipArchive;
        $zip->open($path);
        $xml = $zip->getFromName('word/document.xml');
        $mediaSizes = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name !== false && str_starts_with($name, 'word/media/') && str_ends_with($name, '.png')) {
                $mediaSizes[] = $zip->statIndex($i)['size'];
            }
        }
        $zip->close();
        unlink($path);

        return [$xml, $mediaSizes];
    }

    test('embeds a borderless nested table with the icon at half size when an icon exists', function () {
        $entity = Entity::factory()->create(['icon_id' => null]);

        // /images/site.png ships with the app, so is_file() resolves true via the fallback path.
        [$xml, $mediaSizes] = renderDescriptionCellIntoDocx('Some description', $entity, '/images/site.png');

        expect($mediaSizes)->toHaveCount(1);
        expect($xml)->toContain('Some description');
        // Nested table: a second <w:tbl> inside the outer table's cell, with all borders at sz=0.
        expect(substr_count($xml, '<w:tbl>'))->toBe(2);
        expect($xml)->toContain('<w:tblBorders><w:top w:val="single" w:sz="0" w:color="FFFFFF"/>');
        // Icon halved to TABLE_ICON_SIZE (30pt), right-aligned.
        expect($xml)->toContain('style="width:'.WordHelper::TABLE_ICON_SIZE.'pt; height:'.WordHelper::TABLE_ICON_SIZE.'pt;');
        expect($xml)->toContain('<w:jc w:val="right"/>');
        // Regression guard: a cell-margin-based gap alone proved unreliable in real Word (the
        // default "autofit" table layout can recompute column widths from content and discard a
        // declared margin entirely). The nested table's own total width (5700 twips, see
        // DESCRIPTION_NESTED_TABLE_WIDTH) is deliberately narrower than the outer "value" cell
        // (6000 twips), leaving real unoccupied space to its right that no layout recomputation
        // can eliminate — that's what actually keeps the icon clear of the outer table's border.
        expect($xml)->toContain('<w:tblW w:w="5700" w:type="dxa"/><w:tblLayout w:type="fixed"/>');
        // DESCRIPTION_ICON_RIGHT_MARGIN (private): 60 twips of right padding around the icon.
        expect($xml)->toContain('<w:right w:w="60" w:type="dxa"/>');
    });

    test('falls back to a plain description cell (no nested table) when there is no icon', function () {
        $entity = Entity::factory()->create(['icon_id' => null]);

        [$xml, $mediaSizes] = renderDescriptionCellIntoDocx('Some description', $entity, '/images/this-file-does-not-exist.png');

        expect($mediaSizes)->toHaveCount(0);
        expect($xml)->toContain('Some description');
        expect(substr_count($xml, '<w:tbl>'))->toBe(1);
    });

    test('still shows the icon when the description is empty', function () {
        $entity = Entity::factory()->create(['icon_id' => null]);

        [$xml, $mediaSizes] = renderDescriptionCellIntoDocx(null, $entity, '/images/site.png');

        expect($mediaSizes)->toHaveCount(1);
        expect(substr_count($xml, '<w:tbl>'))->toBe(2);
    });
});

describe('WordHelper::saveDocx — concurrent exports', function () {
    // Régression : avec PclZip, PhpWord écrit chaque partie du package sous un nom FIXE
    // ("document.xml", "styles.xml"...) dans Settings::getTempDir(). Avec le répertoire temporaire
    // partagé, deux exports simultanés s'écrasaient mutuellement (un utilisateur pouvait recevoir
    // le rapport d'un autre). saveDocx() doit utiliser un répertoire privé par enregistrement.
    it('ne touche pas aux fichiers de staging du répertoire temporaire partagé', function () {
        $previousZipClass = Settings::getZipClass();
        Settings::setZipClass(Settings::PCLZIP);

        // Simule le document.xml en cours d'écriture par un autre export concurrent
        $sharedStaging = rtrim(Settings::getTempDir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'document.xml';
        $hadStaging = is_file($sharedStaging);
        $previousContent = $hadStaging ? file_get_contents($sharedStaging) : null;
        file_put_contents($sharedStaging, 'OTHER-EXPORT-CONTENT');

        $path = tempnam(sys_get_temp_dir(), 'docx');

        try {
            $phpWord = (new WordHelper)->newDocument();
            $phpWord->addSection()->addText('MY-EXPORT-CONTENT');

            $tempDirBefore = Settings::getTempDir();
            WordHelper::saveDocx($phpWord, $path);

            // Le fichier de l'autre export est intact et le répertoire temporaire est restauré
            expect(file_get_contents($sharedStaging))->toBe('OTHER-EXPORT-CONTENT')
                ->and(Settings::getTempDir())->toBe($tempDirBefore);

            $zip = new ZipArchive;
            expect($zip->open($path))->toBeTrue();
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();

            expect($xml)->toContain('MY-EXPORT-CONTENT')
                ->not->toContain('OTHER-EXPORT-CONTENT');
        } finally {
            @unlink($path);
            $hadStaging ? file_put_contents($sharedStaging, $previousContent) : @unlink($sharedStaging);
            Settings::setZipClass($previousZipClass);
        }
    });

    it('supprime son répertoire temporaire privé après enregistrement', function () {
        $pattern = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'mercator-phpword-*';
        $before = glob($pattern, GLOB_ONLYDIR);

        $path = tempnam(sys_get_temp_dir(), 'docx');
        try {
            $phpWord = (new WordHelper)->newDocument();
            $phpWord->addSection()->addText('content');
            WordHelper::saveDocx($phpWord, $path);
        } finally {
            @unlink($path);
        }

        expect(glob($pattern, GLOB_ONLYDIR))->toBe($before);
    });
});
