<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\DatoAnual;
use App\Models\Indicador;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

$dryRun = !in_array('--execute', $argv, true);
$filePath = public_path('Indicadores nuevos para carga en el SPED.xlsx');
$planId = 3;

if (!is_file($filePath)) {
    exit("No se encontro el archivo: {$filePath}\n");
}

$spreadsheet = IOFactory::load($filePath);
$rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
$header = array_shift($rows);
$columns = [];
foreach ($header as $column => $label) {
    $columns[trim((string) $label)] = $column;
}

$programs = CatProgramaDerivadoInstitucional::where('plan_estatal', $planId)->get();
$indicators = Indicador::query()->get(['id', 'nombre']);
$institutions = DB::table('instituciones')->get(['id', 'nombre']);

$indicatorKeys = [];
foreach ($indicators as $indicator) {
    $indicatorKeys[normalizeMatch($indicator->nombre)] = $indicator;
}

$newRows = [];
$alreadyExists = [];
$unresolvedPrograms = [];

foreach ($rows as $rowNumber => $row) {
    $name = trim(valueOf($row, $columns, 'Nombre Indicador'));
    if ($name === '') {
        continue;
    }

    $key = normalizeMatch($name);
    if (isset($indicatorKeys[$key])) {
        $alreadyExists[] = ['row' => $rowNumber + 1, 'name' => $name, 'id' => $indicatorKeys[$key]->id];
        continue;
    }

    $program = resolveProgram(
        $programs,
        valueOf($row, $columns, 'Programa Derivado'),
        valueOf($row, $columns, 'Institución')
    );

    if (!$program) {
        $unresolvedPrograms[] = [
            'row' => $rowNumber + 1,
            'indicator' => $name,
            'program' => valueOf($row, $columns, 'Programa Derivado'),
            'institution' => valueOf($row, $columns, 'Institución'),
        ];
        continue;
    }

    $newRows[] = [
        'row' => $rowNumber + 1,
        'row_data' => $row,
        'name' => $name,
        'program' => $program,
        'institution' => resolveInstitution($institutions, valueOf($row, $columns, 'Institución')),
    ];
}

echo "Modo: " . ($dryRun ? 'DRY-RUN' : 'EXECUTE') . PHP_EOL;
echo "Filas de indicadores: " . count($rows) . PHP_EOL;
echo "Indicadores nuevos: " . count($newRows) . PHP_EOL;
echo "Indicadores ya existentes: " . count($alreadyExists) . PHP_EOL;
echo "Programas no resueltos: " . count($unresolvedPrograms) . PHP_EOL;

foreach ($newRows as $item) {
    $annualYears = availableAnnualYears($item['row_data'], $columns);
    echo "- {$item['name']} -> {$item['program']->nombre} ({$annualYears} datos anuales)" . PHP_EOL;
}

if ($unresolvedPrograms) {
    echo PHP_EOL . "PROGRAMAS NO RESUELTOS" . PHP_EOL;
    foreach ($unresolvedPrograms as $item) {
        echo "- {$item['indicator']} | {$item['program']} | {$item['institution']}" . PHP_EOL;
    }
}

if ($dryRun) {
    echo PHP_EOL . "No se realizaron cambios." . PHP_EOL;
    exit(0);
}

if ($unresolvedPrograms) {
    exit("La importacion se detuvo porque hay programas institucionales no resueltos.\n");
}

$created = [];
$annualCreated = 0;
$relationsCreated = 0;

DB::transaction(function () use (&$created, &$annualCreated, &$relationsCreated, $newRows, $columns) {
    foreach ($newRows as $item) {
        $row = $item['row_data'];
        $baseYear = valueOf($row, $columns, 'Linea Base (Año)');
        $baseValue = valueOf($row, $columns, 'Linea Base (Dato)');
        $coverage = valueOf($row, $columns, 'Cobertura') ?: 'Estatal';
        $institution = $item['institution'];

        $indicator = Indicador::create([
            'nombre' => $item['name'],
            'programa_derivado' => $item['program']->nombre,
            'programa' => valueOf($row, $columns, 'Programa') ?: 'PI.1',
            'cod_tematica' => null,
            'tematica' => valueOf($row, $columns, 'Temática') ?: 'Programa Institucional',
            'id_institucion' => $institution?->id,
            'linea_base' => $baseYear,
            'dato_linea_base' => $baseValue,
            'meta_2024' => valueOf($row, $columns, 'Meta 2030'),
            'unidad_medida' => valueOf($row, $columns, 'Unidad de Medida'),
            'fuente' => valueOf($row, $columns, 'Fuente'),
            'liga' => valueOf($row, $columns, 'Enlace') ?: null,
            'descripcion' => valueOf($row, $columns, 'Descripción'),
            'periodicidad' => valueOf($row, $columns, 'Periodicidad'),
            'cobertura' => $coverage,
            'tendencia' => valueOf($row, $columns, 'Tendencia'),
            'fecha_actualizacion' => parseDate(valueOf($row, $columns, 'Fecha Actualización Indicador')),
            'resultados' => valueOf($row, $columns, 'Resultados Generales') ?: null,
            'formula' => valueOf($row, $columns, 'Fórmula'),
            'indicador_validado' => false,
            'indicadorable_type' => null,
            'indicadorable_id' => null,
        ]);

        $indicator->programasInstitucionales()->syncWithoutDetaching([$item['program']->id]);
        $relationsCreated++;

        foreach (range(2010, 2030) as $year) {
            $column = 'Dato ' . $year;
            if (!isset($columns[$column])) {
                continue;
            }

            $value = valueOf($row, $columns, $column);
            if ($value === '') {
                continue;
            }

            DatoAnual::create([
                'id_indicador' => $indicator->id,
                'anio' => $year,
                'valor_dato' => numericValue($value),
                'fecha_actualizacion' => null,
                'resultados' => null,
                'evidencia' => null,
                'observaciones' => null,
                'validado' => false,
                'modificado' => false,
            ]);
            $annualCreated++;
        }

        $created[] = [
            'id' => $indicator->id,
            'name' => $indicator->nombre,
            'program_id' => $item['program']->id,
        ];
    }
});

$report = [
    'file' => $filePath,
    'executed_at' => now()->toIso8601String(),
    'indicators_created' => $created,
    'annual_data_created' => $annualCreated,
    'relations_created' => $relationsCreated,
    'unresolved_programs' => $unresolvedPrograms,
];

$reportPath = storage_path('app/imports/indicadores-nuevos-' . now()->format('Ymd-His') . '.json');
if (!is_dir(dirname($reportPath))) {
    mkdir(dirname($reportPath), 0775, true);
}
file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Indicadores creados: " . count($created) . PHP_EOL;
echo "Datos anuales creados: {$annualCreated}" . PHP_EOL;
echo "Relaciones creadas: {$relationsCreated}" . PHP_EOL;
echo "Reporte: {$reportPath}" . PHP_EOL;

function valueOf(array $row, array $columns, string $label): string
{
    $column = $columns[$label] ?? null;
    return $column === null ? '' : trim((string) ($row[$column] ?? ''));
}

function normalizeMatch(string $value): string
{
    if (class_exists('Normalizer')) {
        $value = Normalizer::normalize($value, Normalizer::FORM_D);
        $value = preg_replace('/\p{Mn}/u', '', $value);
    }

    return preg_replace('/[^a-z0-9]/', '', mb_strtolower($value));
}

function resolveProgram($programs, string $programName, string $institutionName)
{
    $programKey = normalizeMatch($programName);
    $institutionKey = normalizeMatch($institutionName);

    foreach ($programs as $program) {
        $key = normalizeMatch($program->nombre);
        if ($key === $programKey || ($institutionKey !== '' && str_contains($key, $institutionKey))) {
            return $program;
        }
    }

    return null;
}

function resolveInstitution($institutions, string $institutionName)
{
    $key = normalizeMatch($institutionName);
    if ($key === '') {
        return null;
    }

    foreach ($institutions as $institution) {
        $institutionKey = normalizeMatch($institution->nombre);
        if ($institutionKey === $key || str_contains($institutionKey, $key) || str_contains($key, $institutionKey)) {
            return $institution;
        }
    }

    return null;
}

function numericValue(string $value): ?float
{
    $value = str_replace(',', '', trim($value));
    return is_numeric($value) ? (float) $value : null;
}

function parseDate(string $value): ?string
{
    if ($value === '') {
        return null;
    }

    try {
        return date('Y-m-d', strtotime($value));
    } catch (Throwable $e) {
        return null;
    }
}

function availableAnnualYears(array $row, array $columns): int
{
    $count = 0;
    foreach (range(2010, 2030) as $year) {
        $column = 'Dato ' . $year;
        if (isset($columns[$column]) && valueOf($row, $columns, $column) !== '') {
            $count++;
        }
    }
    return $count;
}
