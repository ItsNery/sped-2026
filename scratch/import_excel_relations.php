<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Indicador;
use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\CatProgramaDerivadoSectorial;
use App\Models\CatProgramaDerivadoEspecial;
use App\Models\CatProgramaDerivadoRegional;
use PhpOffice\PhpSpreadsheet\IOFactory;

// Change to false if running with --execute argument
$dryRun = true;
if (isset($argv[1]) && $argv[1] === '--execute') {
    $dryRun = false;
}

$filePath = "C:\\Users\\nery.pozos\\Desktop\\SPEd Nuevos inst\\Relacion Indic-Institu.xlsx";

echo "========================================================\n";
echo "RELATION IMPORT SCRIPT (" . ($dryRun ? "DRY-RUN MODE" : "WRITE MODE") . ")\n";
echo "========================================================\n";
if ($dryRun) {
    echo "💡 TIP: Run with '--execute' to perform the database writes, e.g.:\n";
    echo "   php scratch/import_excel_relations.php --execute\n";
}
echo "========================================================\n\n";

// 1. Load all programs from database
$allPrograms = [];

$institucionales = CatProgramaDerivadoInstitucional::all();
foreach ($institucionales as $p) {
    $allPrograms[] = [
        'model' => $p,
        'class' => CatProgramaDerivadoInstitucional::class,
        'normalized' => normalizeName($p->nombre),
        'original' => $p->nombre
    ];
}

$sectoriales = CatProgramaDerivadoSectorial::all();
foreach ($sectoriales as $p) {
    $allPrograms[] = [
        'model' => $p,
        'class' => CatProgramaDerivadoSectorial::class,
        'normalized' => normalizeName($p->nombre),
        'original' => $p->nombre
    ];
}

$especiales = CatProgramaDerivadoEspecial::all();
foreach ($especiales as $p) {
    $allPrograms[] = [
        'model' => $p,
        'class' => CatProgramaDerivadoEspecial::class,
        'normalized' => normalizeName($p->nombre),
        'original' => $p->nombre
    ];
}

$regionales = CatProgramaDerivadoRegional::all();
foreach ($regionales as $p) {
    $allPrograms[] = [
        'model' => $p,
        'class' => CatProgramaDerivadoRegional::class,
        'normalized' => normalizeName($p->nombre),
        'original' => $p->nombre
    ];
}

// Sort all programs by normalized name length descending to avoid partial matches
usort($allPrograms, function($a, $b) {
    return strlen($b['normalized']) - strlen($a['normalized']);
});

echo "Loaded " . count($allPrograms) . " programs from the database catalog.\n\n";

// 2. Load all indicators from DB and map by normalized key
$dbIndicators = [];
foreach (Indicador::all() as $ind) {
    $key = cleanNameForMatching($ind->nombre);
    $dbIndicators[$key] = $ind;
}

// 3. Load Excel sheet
try {
    $reader = IOFactory::createReaderForFile($filePath);
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($filePath);
    $sheet = $spreadsheet->getSheetByName("Lista repetidos");
    if (!$sheet) {
        $sheet = $spreadsheet->getActiveSheet();
        echo "Sheet 'Lista repetidos' not found, using active sheet.\n";
    }
    
    $rows = $sheet->toArray();
    // Shift headers
    array_shift($rows);
} catch (\Exception $e) {
    die("Error loading Excel: " . $e->getMessage() . "\n");
}

$totalProcessed = 0;
$notFoundList = [];
$updated = 0;

foreach ($rows as $index => $row) {
    $nombreIndicador = trim($row[0] ?? '');
    $listaProgramasTxt = trim($row[1] ?? '');
    
    if (empty($nombreIndicador) || strtolower($nombreIndicador) === 'total general') {
        continue;
    }
    
    $totalProcessed++;
    $rowNumber = $index + 2; // header + 1-index
    
    // Find the indicator in the database using fuzzy match
    $searchKey = cleanNameForMatching($nombreIndicador);
    $indicador = $dbIndicators[$searchKey] ?? null;
    
    if (!$indicador) {
        $notFoundList[] = "Row {$rowNumber}: \"{$nombreIndicador}\"";
        continue;
    }
    
    // Scan text in Column B for program matches
    $textToSearch = cleanTextForSearch($listaProgramasTxt);
    $matchedInstitucionales = [];
    $matchedOriginales = [];
    
    foreach ($allPrograms as $progData) {
        if (strpos($textToSearch, $progData['normalized']) !== false) {
            if ($progData['class'] === CatProgramaDerivadoInstitucional::class) {
                $matchedInstitucionales[] = $progData['model'];
            } else {
                $matchedOriginales[] = $progData['model'];
            }
            // Remove from search text to avoid double matching shorter substrings
            $textToSearch = str_replace($progData['normalized'], '', $textToSearch);
        }
    }
    
    echo "Row {$rowNumber}: 📝 \"{$indicador->nombre}\"\n";
    echo "  Excel text: \"{$listaProgramasTxt}\"\n";
    
    // Output matches
    if (count($matchedOriginales) > 0) {
        $orig = $matchedOriginales[0];
        echo "  -> Primary Derived Program detected: \"{$orig->nombre}\" (" . basename(get_class($orig)) . ")\n";
    } else {
        echo "  -> Primary Derived Program detected: None (Purely Institutional)\n";
    }
    
    if (count($matchedInstitucionales) > 0) {
        echo "  -> Linked Institutional Programs (" . count($matchedInstitucionales) . "):\n";
        foreach ($matchedInstitucionales as $inst) {
            echo "     - \"{$inst->nombre}\" (Siglas: {$inst->siglas})\n";
        }
    } else {
        echo "  -> Linked Institutional Programs: None\n";
    }
    
    // Perform updates if not in dry-run mode
    if (!$dryRun) {
        // 1. Update Polymorphic relation
        if (count($matchedOriginales) > 0) {
            $orig = $matchedOriginales[0];
            $indicador->indicadorable_id = $orig->id;
            $indicador->indicadorable_type = get_class($orig);
            $indicador->programa_derivado = $orig->nombre;
        } else {
            $indicador->indicadorable_id = null;
            $indicador->indicadorable_type = null;
            if (count($matchedInstitucionales) > 0) {
                $indicador->programa_derivado = $matchedInstitucionales[0]->nombre;
            } else {
                $indicador->programa_derivado = $indicador->programa_derivado ?: 'Programa Institucional';
            }
        }
        
        // 2. Sync pivot table
        $instIds = array_map(function($m) { return $m->id; }, $matchedInstitucionales);
        $indicador->programasInstitucionales()->sync($instIds);
        
        $indicador->save();
        $updated++;
    }
    echo "--------------------------------------------------------\n";
}

echo "\n================ Summary ================\n";
echo "Total Rows Processed: {$totalProcessed}\n";
echo "Indicators Not Found Count: " . count($notFoundList) . "\n";
if (count($notFoundList) > 0) {
    echo "Not Found Indicators List:\n";
    foreach ($notFoundList as $item) {
        echo "  - {$item}\n";
    }
}
if (!$dryRun) {
    echo "Indicators Updated in DB: {$updated}\n";
} else {
    echo "Dry Run mode: No changes were written to the database.\n";
}

// Helper normalization functions
function cleanTextForSearch($text) {
    if (class_exists('Normalizer')) {
        $text = \Normalizer::normalize($text, \Normalizer::FORM_KD);
        $text = preg_replace('/\p{Mn}/u', '', $text);
    }
    $text = str_replace([',', '.', ';'], ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim(mb_strtolower($text));
}

function normalizeName($name) {
    $cleaned = preg_replace('/^(Programa Institucional|Programa Sectorial|Programa Especial|Programa Regional)\s+(de\s+la\s+|del\s+|de\s+|para\s+)?/i', '', $name);
    return cleanTextForSearch($cleaned);
}

function cleanNameForMatching($name) {
    if (class_exists('Normalizer')) {
        $name = \Normalizer::normalize($name, \Normalizer::FORM_KD);
        $name = preg_replace('/\p{Mn}/u', '', $name);
    }
    $name = preg_replace('/[^a-zA-Z0-9]/', '', $name);
    return trim(strtolower($name));
}
