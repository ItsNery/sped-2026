<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\Indicador;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

$dryRun = !in_array('--execute', $argv, true);
$filePath = public_path('Relacion nuevis derivados indicadr.ods');
$planId = (int) config('sped.active_plan_id', 3);
$pivotTable = 'programa_institucional_indicador';

if (!is_file($filePath)) {
    exit("No se encontro el archivo: {$filePath}\n");
}

if (!DB::table('cat_planes_estatales_desarrollo')->where('id', $planId)->exists()) {
    exit("No se encontro el plan estatal con ID {$planId}.\n");
}

if (!DB::getSchemaBuilder()->hasTable($pivotTable)) {
    exit("No se encontro la tabla {$pivotTable}.\n");
}

$spreadsheet = IOFactory::load($filePath);
$rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
$header = array_shift($rows);

$programs = [];
$rawRows = 0;
$ignoredRows = [];
$unknownTypes = [];

foreach ($rows as $index => $row) {
    $rawRows++;
    $type = cleanText((string) ($row['A'] ?? ''));
    $sourceName = trim((string) ($row['B'] ?? ''));
    $indicatorName = trim((string) ($row['C'] ?? ''));

    if ($type === '' && $sourceName === '' && $indicatorName === '') {
        continue;
    }

    if ($type !== 'institucional') {
        if ($type !== '') {
            $unknownTypes[$type] = ($unknownTypes[$type] ?? 0) + 1;
        }
        $ignoredRows[] = $index + 1;
        continue;
    }

    if ($sourceName === '' || $indicatorName === '') {
        $ignoredRows[] = $index + 1;
        continue;
    }

    $programKey = normalizeEntityName($sourceName);
    $indicatorKey = normalizeMatch($indicatorName);

    if (!isset($programs[$programKey])) {
        $programs[$programKey] = [
            'source_name' => $sourceName,
            'name' => buildProgramName($sourceName),
            'group' => classifyGroup($sourceName),
            'rows' => [],
            'indicator_keys' => [],
        ];
    }

    $programs[$programKey]['rows'][] = $index + 1;
    $programs[$programKey]['indicator_keys'][$indicatorKey] = $indicatorName;
}

$indicatorsByKey = [];
$duplicateIndicators = [];
foreach (Indicador::query()->get(['id', 'nombre']) as $indicator) {
    $key = normalizeMatch($indicator->nombre);
    if (isset($indicatorsByKey[$key])) {
        $duplicateIndicators[$key][] = $indicator->id;
        continue;
    }
    $indicatorsByKey[$key] = $indicator;
}

$existingPrograms = [];
$existingDuplicates = [];
foreach (CatProgramaDerivadoInstitucional::where('plan_estatal', $planId)->get() as $program) {
    $key = normalizeEntityName($program->nombre);
    if (isset($existingPrograms[$key])) {
        $existingDuplicates[$key][] = $program->id;
        continue;
    }
    $existingPrograms[$key] = $program;
}

$existingRelations = [];
foreach (DB::table($pivotTable)->get(['indicador_id', 'programa_institucional_id']) as $relation) {
    $existingRelations[$relation->programa_institucional_id . ':' . $relation->indicador_id] = true;
}

$newPrograms = [];
$reusedPrograms = [];
$newRelations = [];
$duplicateRelations = [];
$missingIndicators = [];
$ambiguousIndicators = [];
$defaultClassifications = [];

foreach ($programs as $programKey => $programData) {
    if (isset($existingPrograms[$programKey])) {
        $program = $existingPrograms[$programKey];
        $reusedPrograms[] = [
            'id' => $program->id,
            'name' => $program->nombre,
            'source_name' => $programData['source_name'],
        ];
    } else {
        $program = null;
        $newPrograms[] = [
            'name' => $programData['name'],
            'source_name' => $programData['source_name'],
            'group' => $programData['group'],
        ];
    }

    if ($programData['group'] === 'Organismos Auxiliares' && !startsWithSecretaria($programData['source_name'])) {
        $defaultClassifications[] = [
            'source_name' => $programData['source_name'],
            'group' => $programData['group'],
        ];
    }

    foreach ($programData['indicator_keys'] as $indicatorKey => $indicatorName) {
        if (!isset($indicatorsByKey[$indicatorKey])) {
            if (isset($duplicateIndicators[$indicatorKey])) {
                $ambiguousIndicators[] = [
                    'program' => $programData['name'],
                    'indicator' => $indicatorName,
                    'ids' => $duplicateIndicators[$indicatorKey],
                ];
            } else {
                $missingIndicators[] = [
                    'program' => $programData['name'],
                    'indicator' => $indicatorName,
                ];
            }
            continue;
        }

        if ($program && isset($existingRelations[$program->id . ':' . $indicatorsByKey[$indicatorKey]->id])) {
            $duplicateRelations[] = [
                'program' => $program->nombre,
                'indicator' => $indicatorName,
            ];
        } else {
            $newRelations[] = [
                'program_key' => $programKey,
                'program_id' => $program?->id,
                'program_name' => $programData['name'],
                'indicator_id' => $indicatorsByKey[$indicatorKey]->id,
                'indicator_name' => $indicatorName,
            ];
        }
    }
}

echo "Modo: " . ($dryRun ? 'DRY-RUN' : 'EXECUTE') . PHP_EOL;
echo "Filas leidas: {$rawRows}" . PHP_EOL;
echo "Programas institucionales unicos: " . count($programs) . PHP_EOL;
echo "Programas existentes reutilizados: " . count($reusedPrograms) . PHP_EOL;
echo "Programas nuevos: " . count($newPrograms) . PHP_EOL;
echo "Relaciones nuevas: " . count($newRelations) . PHP_EOL;
echo "Relaciones ya existentes: " . count($duplicateRelations) . PHP_EOL;
echo "Indicadores no encontrados: " . count($missingIndicators) . PHP_EOL;
echo "Indicadores ambiguos: " . count($ambiguousIndicators) . PHP_EOL;
echo "Clasificaciones por defecto: " . count($defaultClassifications) . PHP_EOL;
echo "Tipos ignorados: " . json_encode($unknownTypes, JSON_UNESCAPED_UNICODE) . PHP_EOL;

if ($missingIndicators) {
    echo PHP_EOL . "INDICADORES NO ENCONTRADOS" . PHP_EOL;
    foreach ($missingIndicators as $missing) {
        echo "- {$missing['indicator']} | {$missing['program']}" . PHP_EOL;
    }
}

if ($ambiguousIndicators) {
    echo PHP_EOL . "INDICADORES AMBIGUOS" . PHP_EOL;
    foreach ($ambiguousIndicators as $ambiguous) {
        echo "- {$ambiguous['indicator']} | IDs: " . implode(', ', $ambiguous['ids']) . PHP_EOL;
    }
}

if ($newPrograms) {
    echo PHP_EOL . "PROGRAMAS NUEVOS" . PHP_EOL;
    foreach ($newPrograms as $program) {
        echo "- {$program['name']} | grupo: {$program['group']}" . PHP_EOL;
    }
}

if ($dryRun) {
    echo PHP_EOL . "No se realizaron cambios." . PHP_EOL;
    exit(0);
}

if ($ambiguousIndicators) {
    exit("La importacion se detuvo: hay indicadores ambiguos.\n");
}

$createdPrograms = [];
$insertedRelations = 0;

DB::transaction(function () use (&$createdPrograms, &$insertedRelations, $programs, $existingPrograms, $newRelations, $pivotTable, $planId) {
    $programIds = [];

    foreach ($programs as $programKey => $programData) {
        if (isset($existingPrograms[$programKey])) {
            $programIds[$programKey] = $existingPrograms[$programKey]->id;
            continue;
        }

        $program = CatProgramaDerivadoInstitucional::create([
            'nombre' => $programData['name'],
            'grupo' => $programData['group'],
            'siglas' => null,
            'imagen' => 'img/pleca-pajaro-2.png',
            'descripcion' => 'Programa Institucional del Plan Estatal de Desarrollo 2024-2030.',
            'color' => '#691A32',
            'plan_estatal' => $planId,
            'documento' => 'https://ped2024-2030.puebla.gob.mx/',
        ]);

        $program->siglas = $program->siglas;
        $program->save();
        $programIds[$programKey] = $program->id;
        $createdPrograms[] = $program->id;
    }

    foreach ($newRelations as $relation) {
        $programId = $programIds[$relation['program_key']];
        $exists = DB::table($pivotTable)
            ->where('programa_institucional_id', $programId)
            ->where('indicador_id', $relation['indicator_id'])
            ->exists();

        if ($exists) {
            continue;
        }

        DB::table($pivotTable)->insert([
            'indicador_id' => $relation['indicator_id'],
            'programa_institucional_id' => $programId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $insertedRelations++;
    }
});

$report = [
    'file' => $filePath,
    'executed_at' => now()->toIso8601String(),
    'programs_created' => $createdPrograms,
    'relations_inserted' => $insertedRelations,
    'missing_indicators' => $missingIndicators,
    'ignored_types' => $unknownTypes,
];

$reportPath = storage_path('app/imports/programas-institucionales-' . now()->format('Ymd-His') . '.json');
if (!is_dir(dirname($reportPath))) {
    mkdir(dirname($reportPath), 0775, true);
}
file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo PHP_EOL . "Programas creados: " . count($createdPrograms) . PHP_EOL;
echo "Relaciones insertadas: {$insertedRelations}" . PHP_EOL;
echo "Reporte: {$reportPath}" . PHP_EOL;

function cleanText(string $value): string
{
    if (class_exists('Normalizer')) {
        $value = Normalizer::normalize($value, Normalizer::FORM_D);
        $value = preg_replace('/\p{Mn}/u', '', $value);
    }

    return trim(mb_strtolower(preg_replace('/\s+/', ' ', $value)));
}

function normalizeMatch(string $value): string
{
    return preg_replace('/[^a-z0-9]/', '', cleanText($value));
}

function normalizeEntityName(string $value): string
{
    $value = cleanText($value);
    $value = preg_replace('/^programa institucional (del |de la |de )/', '', $value);
    return normalizeMatch($value);
}

function buildProgramName(string $sourceName): string
{
    $sourceName = trim(preg_replace('/\s+/', ' ', $sourceName));
    if (preg_match('/^programa institucional\b/i', $sourceName)) {
        return $sourceName;
    }

    $delPrefixes = ['instituto', 'sistema', 'colegio', 'consejo', 'centro', 'comite', 'fideicomiso', 'fondo'];
    $dePrefixes = ['convenciones', 'carreteras', 'servicios', 'museos', 'capital'];
    $firstWord = cleanText((string) strtok($sourceName, ' '));
    $article = in_array($firstWord, $delPrefixes, true)
        ? 'del'
        : (in_array($firstWord, $dePrefixes, true) ? 'de' : 'de la');

    return "Programa Institucional {$article} {$sourceName}";
}

function classifyGroup(string $sourceName): string
{
    $name = cleanText($sourceName);
    if (str_starts_with($name, 'secretaria ') || str_starts_with($name, 'consejeria juridica') || str_starts_with($name, 'coordinacion general')) {
        return 'Secretarías';
    }

    return 'Organismos Auxiliares';
}

function startsWithSecretaria(string $sourceName): bool
{
    $name = cleanText($sourceName);
    return str_starts_with($name, 'secretaria ') || str_starts_with($name, 'consejeria juridica') || str_starts_with($name, 'coordinacion general');
}
