<?php

namespace App\Services;

use App\Models\CatEje;
use App\Models\CatProgramaDerivadoEspecial;
use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\CatProgramaDerivadoRegional;
use App\Models\CatProgramaDerivadoSectorial;
use App\Models\DatoAnual;
use App\Models\Indicador;
use App\Models\Institucion;
use App\Models\Odses;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use Throwable;

class HistoricalPedImportService
{
    private const PLAN_TYPE = 'Plan Estatal de Desarrollo';

    private const PROGRAM_TYPES = [
        'Programa Sectorial' => CatProgramaDerivadoSectorial::class,
        'Programa Especial' => CatProgramaDerivadoEspecial::class,
        'Programa Regional' => CatProgramaDerivadoRegional::class,
        'Programa Institucional' => CatProgramaDerivadoInstitucional::class,
    ];

    private const YEARS = [
        2010, 2011, 2012, 2013, 2014,
        2015, 2016, 2017, 2018, 2019,
        2020, 2021, 2022, 2023, 2024,
    ];

    public function run(int $planId, string $filePath, bool $execute = false): array
    {
        $report = [
            'plan_id' => $planId,
            'file' => $filePath,
            'mode' => $execute ? 'execute' : 'dry-run',
            'source_rows' => 0,
            'valid_rows' => 0,
            'invalid_rows' => 0,
            'rows_by_type' => [],
            'unique_catalogs' => [
                'ejes' => 0,
                'programas' => 0,
            ],
            'annual_values' => 0,
            'invalid_ods' => [],
            'field_issues' => [
                'unidad_medida' => [],
                'cobertura' => [],
                'tendencia' => [],
            ],
            'field_warnings' => [
                'cobertura' => [],
            ],
            'unresolved_institutions' => [],
            'unresolved_institution_indicators' => [],
            'errors' => [],
            'created' => [
                'ejes' => 0,
                'programas' => 0,
                'indicadores' => 0,
                'datos_anuales' => 0,
                'relaciones_institucionales' => 0,
            ],
            'updated' => [
                'indicadores' => 0,
                'datos_anuales' => 0,
            ],
            'indicator_ids' => [],
            'catalog_ids' => [],
        ];

        if (!is_file($filePath)) {
            $report['errors'][] = "No se encontro el archivo: {$filePath}";
            return $report;
        }

        try {
            $rows = $this->readRows($filePath);
        } catch (Throwable $exception) {
            $report['errors'][] = 'No se pudo leer el Excel: ' . $exception->getMessage();
            return $report;
        }

        $report['source_rows'] = count($rows);
        $normalizedRows = [];
        $catalogKeys = [];
        $validOds = Odses::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $axisNumbers = [];

        foreach ($rows as $index => $row) {
            try {
                $normalized = $this->normalizeRow($row, $validOds);
                if ($normalized['type'] === self::PLAN_TYPE) {
                    $axisKey = $this->key($normalized['parent_name']);
                    if (!isset($axisNumbers[$axisKey])) {
                        $axisNumbers[$axisKey] = count($axisNumbers) + 1;
                    }
                    $normalized['axis_number'] = $normalized['axis_number'] ?: $axisNumbers[$axisKey];
                }
                $normalized['source_row'] = $index + 2;
                $normalizedRows[] = $normalized;

                $this->recordFieldIssue(
                    $report['field_issues']['unidad_medida'],
                    'unidad_medida',
                    $row['unidaddemedida'] ?? $row['unidadmedida'] ?? null,
                    $normalized
                );
                $this->recordFieldIssue(
                    $report['field_issues']['cobertura'],
                    'cobertura',
                    $row['cobertura'] ?? null,
                    $normalized
                );
                $this->recordFieldIssue(
                    $report['field_issues']['tendencia'],
                    'tendencia',
                    $row['tendencia'] ?? null,
                    $normalized
                );

                if ($this->key((string) ($row['cobertura'] ?? '')) === 'quinquenal') {
                    $report['field_warnings']['cobertura'][] = [
                        'source_row' => $normalized['source_row'],
                        'indicator' => $normalized['name'],
                        'type' => $normalized['type'],
                        'program' => $normalized['parent_name'],
                        'periodicity' => $this->text($row['periodicidad'] ?? null),
                        'value' => $this->text($row['cobertura'] ?? null),
                    ];
                }

                $report['rows_by_type'][$normalized['type']] = ($report['rows_by_type'][$normalized['type']] ?? 0) + 1;
                $report['annual_values'] += count($normalized['annual_values']);

                if ($normalized['type'] === self::PLAN_TYPE) {
                    $catalogKeys['ejes'][$this->key($normalized['parent_name'])] = true;
                } else {
                    $catalogKeys['programas'][$normalized['type'] . '|' . $this->key($normalized['parent_name'])] = true;
                }

                foreach ($normalized['invalid_ods'] as $invalidOds) {
                    $report['invalid_ods'][$invalidOds] = ($report['invalid_ods'][$invalidOds] ?? 0) + 1;
                }

                $institutionName = $normalized['institution_name'];
                $institution = $institutionName !== '' ? $this->resolveInstitution($institutionName) : null;

                if (!$institution) {
                    $reportInstitution = $institutionName !== ''
                        ? $institutionName
                        : 'No especificada en el archivo';
                    $report['unresolved_institutions'][$reportInstitution] =
                        ($report['unresolved_institutions'][$reportInstitution] ?? 0) + 1;
                    $report['unresolved_institution_indicators'][] = [
                        'source_row' => $normalized['source_row'],
                        'indicator' => $normalized['name'],
                        'institution' => $reportInstitution,
                        'type' => $normalized['type'],
                        'program' => $normalized['parent_name'],
                        'tematica' => $normalized['tematica'],
                    ];
                }
            } catch (Throwable $exception) {
                $report['invalid_rows']++;
                $report['errors'][] = 'Fila ' . ($index + 2) . ': ' . $exception->getMessage();
            }
        }

        $report['valid_rows'] = count($normalizedRows);
        $report['unique_catalogs']['ejes'] = count($catalogKeys['ejes'] ?? []);
        $report['unique_catalogs']['programas'] = count($catalogKeys['programas'] ?? []);
        ksort($report['rows_by_type']);
        ksort($report['invalid_ods']);
        ksort($report['unresolved_institutions']);

        if (!$execute || $report['errors']) {
            return $report;
        }

        try {
            $execution = DB::transaction(function () use ($planId, $normalizedRows) {
                return $this->executeRows($planId, $normalizedRows);
            });

            $report['created'] = $execution['created'];
            $report['updated'] = $execution['updated'];
            $report['indicator_ids'] = $execution['indicator_ids'];
            $report['catalog_ids'] = $execution['catalog_ids'];
        } catch (Throwable $exception) {
            $report['errors'][] = 'La transaccion fue revertida: ' . $exception->getMessage();
        }

        return $report;
    }

    private function readRows(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('BD_COMPLETA') ?: $spreadsheet->getActiveSheet();
        $rawRows = $sheet->toArray(null, true, true, false);

        if (!$rawRows) {
            throw new RuntimeException('La hoja de datos esta vacia.');
        }

        $header = array_shift($rawRows);
        $columns = [];
        foreach ($header as $index => $label) {
            $columns[$this->key((string) $label)] = $index;
        }

        foreach (['indicador', 'programaderivado', 'programa'] as $required) {
            if (!array_key_exists($required, $columns)) {
                throw new RuntimeException("Falta la columna requerida: {$required}.");
            }
        }

        return array_values(array_filter(array_map(function (array $row) use ($columns) {
            $mapped = [];
            foreach ($columns as $key => $index) {
                $mapped[$key] = $row[$index] ?? null;
            }
            return $mapped;
        }, $rawRows), function (array $row) {
            return count(array_filter($row, fn ($value) => trim((string) $value) !== '')) > 0;
        }));
    }

    private function normalizeRow(array $row, array $validOds): array
    {
        $left = $this->text($row['programaderivado'] ?? null);
        $right = $this->text($row['programa'] ?? null);
        $type = $this->resolveType($left, $right);

        if (!$type) {
            throw new RuntimeException('No se pudo determinar el tipo de programa.');
        }

        $parentName = $type === 'Programa Regional'
            ? $this->resolveRegionalName(
                $this->text($row['tematica'] ?? null),
                $this->text($row['indicador'] ?? null)
            )
            : $this->resolveParentName($left, $right, $type);
        if ($parentName === '') {
            throw new RuntimeException('El eje o programa relacionado esta vacio.');
        }

        $name = $this->text($row['indicador'] ?? null);
        if ($name === '') {
            throw new RuntimeException('El nombre del indicador esta vacio.');
        }

        $invalidOds = [];
        $ods = [];
        foreach (preg_split('/[,;]+/', $this->text($row['ods'] ?? null)) as $value) {
            $value = trim($value);
            if ($value === '') {
                continue;
            }

            $id = (int) $value;
            if (!ctype_digit($value) || !in_array($id, $validOds, true)) {
                $invalidOds[] = $value;
                continue;
            }

            $ods[] = $id;
        }

        $annualValues = [];
        foreach (self::YEARS as $offset => $year) {
            $value = $this->numeric($row[(string) $year] ?? null);
            if ($value !== null) {
                $annualValues[$year] = $value;
            }
        }

        $isPlan = $type === self::PLAN_TYPE;
        $programa = $isPlan
            ? $parentName
            : ($type === 'Programa Regional' ? $this->resolveParentName($left, $right, $type) : $type);

        return [
            'name' => $name,
            'type' => $type,
            'parent_name' => $parentName,
            'programa_derivado' => $isPlan ? self::PLAN_TYPE : $parentName,
            'programa' => $programa,
            'tematica' => $this->requiredText($row['tematica'] ?? null),
            'linea_base' => $this->requiredText($row['lineabase'] ?? null),
            'dato_linea_base' => $this->requiredText($row['datolineabase'] ?? $row['datolinea'] ?? null),
            'unidad_medida' => $this->requiredText($row['unidaddemedida'] ?? $row['unidadmedida'] ?? null),
            'meta' => $this->requiredText($row['meta2024'] ?? null),
            'fuente' => $this->nullableText($row['fuente'] ?? null),
            'liga' => $this->nullableText($row['link'] ?? $row['url'] ?? $row['liga'] ?? null),
            'descripcion' => $this->nullableText($row['descripcion'] ?? null),
            'periodicidad' => $this->requiredText($row['periodicidad'] ?? null),
            'cobertura' => $this->requiredText($row['cobertura'] ?? null),
            'tendencia' => $this->normalizeTrend($row['tendencia'] ?? null),
            'resultados' => $this->nullableText($row['principalesresultados'] ?? $row['resultados'] ?? null),
            'formula' => $this->nullableText($row['formula'] ?? null),
            'fecha_actualizacion' => $this->date($row['fechaactualizacion'] ?? null),
            'ods' => array_values(array_unique($ods)),
            'invalid_ods' => array_values(array_unique($invalidOds)),
            'annual_values' => $annualValues,
            'axis_number' => $this->axisNumber($row['tematica'] ?? null),
            'institution_name' => $this->text($row['institucionresponsable'] ?? $row['institucion'] ?? null),
        ];
    }

    private function executeRows(int $planId, array $rows): array
    {
        $created = [
            'ejes' => 0,
            'programas' => 0,
            'indicadores' => 0,
            'datos_anuales' => 0,
            'relaciones_institucionales' => 0,
        ];
        $updated = [
            'indicadores' => 0,
            'datos_anuales' => 0,
        ];
        $indicatorIds = [];
        $catalogIds = [];
        $catalogCache = [];
        $indicatorCache = [];
        $institutionCache = [];

        foreach ($rows as $row) {
            $catalogKey = $row['type'] . '|' . $this->key($row['parent_name']);
            $catalog = $catalogCache[$catalogKey] ?? null;

            if (!$catalog) {
                $catalog = $row['type'] === self::PLAN_TYPE
                    ? $this->resolveAxis($planId, $row)
                    : $this->resolveProgram($planId, $row);
                $catalogCache[$catalogKey] = $catalog;
                $catalogIds[] = $catalog->id;
                if ($catalog->wasRecentlyCreated) {
                    $row['type'] === self::PLAN_TYPE ? $created['ejes']++ : $created['programas']++;
                }
            }

            $indicatorKey = $catalogKey . '|' . $this->key($row['tematica']) . '|' . $this->key($row['name']);
            $indicator = $indicatorCache[$indicatorKey] ?? null;
            if (!$indicator) {
                $indicator = $this->findExistingIndicator($row, $catalog);
            }

            $attributes = [
                'nombre' => $row['name'],
                'programa_derivado' => $row['programa_derivado'],
                'programa' => $row['programa'],
                'tematica' => $row['tematica'],
                'linea_base' => $row['linea_base'],
                'dato_linea_base' => $row['dato_linea_base'],
                'meta_anio' => $planId === 3 ? 2030 : 2024,
                'meta' => $row['meta'],
                'unidad_medida' => $row['unidad_medida'],
                'fuente' => $row['fuente'],
                'liga' => $row['liga'],
                'descripcion' => $row['descripcion'],
                'periodicidad' => $row['periodicidad'],
                'cobertura' => $row['cobertura'],
                'tendencia' => $row['tendencia'],
                'fecha_actualizacion' => $row['fecha_actualizacion'],
                'resultados' => $row['resultados'],
                'formula' => $row['formula'],
                'indicador_validado' => true,
            ];

            if ($row['institution_name'] !== '') {
                if (!array_key_exists($row['institution_name'], $institutionCache)) {
                    $institutionCache[$row['institution_name']] = $this->resolveInstitution($row['institution_name']);
                }
                $attributes['id_institucion'] = $institutionCache[$row['institution_name']]?->id;
            }

            if ($row['type'] === 'Programa Institucional') {
                $attributes['indicadorable_type'] = null;
                $attributes['indicadorable_id'] = null;
            } else {
                $attributes['indicadorable_type'] = get_class($catalog);
                $attributes['indicadorable_id'] = $catalog->id;
            }

            if ($indicator) {
                $indicator->fill($attributes);
                $indicator->save();
                $updated['indicadores']++;
            } else {
                $indicator = Indicador::create($attributes);
                $created['indicadores']++;
            }

            $indicatorCache[$indicatorKey] = $indicator;
            $indicatorIds[] = $indicator->id;

            if ($row['type'] === 'Programa Institucional') {
                $relationExists = DB::table('programa_institucional_indicador')
                    ->where('indicador_id', $indicator->id)
                    ->where('programa_institucional_id', $catalog->id)
                    ->exists();
                $indicator->programasInstitucionales()->syncWithoutDetaching([$catalog->id]);
                if (!$relationExists) {
                    $created['relaciones_institucionales']++;
                }
            }

            if ($row['ods']) {
                $indicator->ods()->sync($row['ods']);
            }

            foreach ($row['annual_values'] as $year => $value) {
                $annual = DatoAnual::withoutEvents(function () use ($indicator, $year, $value) {
                    return DatoAnual::updateOrCreate(
                        ['id_indicador' => $indicator->id, 'anio' => $year],
                        [
                            'valor_dato' => $value,
                            'validado' => true,
                            'modificado' => false,
                        ]
                    );
                });

                $annual->wasRecentlyCreated ? $created['datos_anuales']++ : $updated['datos_anuales']++;
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'indicator_ids' => array_values(array_unique($indicatorIds)),
            'catalog_ids' => array_values(array_unique($catalogIds)),
        ];
    }

    private function resolveAxis(int $planId, array $row): CatEje
    {
        $axis = CatEje::where('plan_id', $planId)->get()->first(
            fn (CatEje $eje) => $this->key($eje->nombre) === $this->key($row['parent_name'])
        );

        if ($axis) {
            return $axis;
        }

        $number = $row['axis_number'] ?: (CatEje::where('plan_id', $planId)->max('numero') + 1);

        return CatEje::create([
            'nombre' => $row['parent_name'],
            'numero' => $number,
            'color' => '#6c757d',
            'plan_id' => $planId,
        ]);
    }

    private function resolveProgram(int $planId, array $row)
    {
        $model = self::PROGRAM_TYPES[$row['type']] ?? null;
        if (!$model) {
            throw new RuntimeException("Tipo de programa no soportado: {$row['type']}.");
        }

        $program = $model::where('plan_estatal', $planId)->get()->first(
            fn ($item) => $this->key($item->nombre) === $this->key($row['parent_name'])
        );

        if ($program) {
            return $program;
        }

        $attributes = [
            'nombre' => $row['parent_name'],
            'imagen' => 'img/pleca-pajaro-2.png',
            'descripcion' => 'Programa derivado historico importado del PED 2019-2024.',
            'color' => '#6c757d',
            'icono' => null,
            'plan_estatal' => $planId,
            'documento' => null,
        ];

        if ($model === CatProgramaDerivadoRegional::class) {
            $attributes['documento'] = '';
        }

        if ($model === CatProgramaDerivadoInstitucional::class) {
            $attributes['grupo'] = 'Historicos';
            $attributes['siglas'] = null;
        }

        return $model::create($attributes);
    }

    private function findExistingIndicator(array $row, $catalog): ?Indicador
    {
        $query = Indicador::where('nombre', $row['name'])
            ->where('tematica', $row['tematica']);

        if ($row['type'] === 'Programa Institucional') {
            return $query->whereHas(
                'programasInstitucionales',
                fn ($program) => $program->whereKey($catalog->id)
            )->first();
        }

        return $query->where('indicadorable_type', get_class($catalog))
            ->where('indicadorable_id', $catalog->id)
            ->first();
    }

    private function resolveType(string $left, string $right): ?string
    {
        foreach (array_keys(self::PROGRAM_TYPES) as $type) {
            if ($this->typeKey($left) === $this->typeKey($type)) {
                return $type;
            }
            if ($this->typeKey($right) === $this->typeKey($type)) {
                return $type;
            }
        }

        return $this->typeKey($left) === $this->typeKey(self::PLAN_TYPE)
            || $this->typeKey($right) === $this->typeKey(self::PLAN_TYPE)
            ? self::PLAN_TYPE
            : null;
    }

    private function typeKey(string $value): string
    {
        return preg_replace('/2$/', '', $this->key($value));
    }

    private function resolveParentName(string $left, string $right, string $type): string
    {
        return $this->key($left) === $this->key($type) ? $right : $left;
    }

    private function axisNumber(?string $value): ?int
    {
        return preg_match('/eje\s*(\d+)/i', (string) $value, $matches)
            ? (int) $matches[1]
            : null;
    }

    private function resolveRegionalName(string $theme, string $indicatorName): string
    {
        $themeKey = $this->key($theme);

        if ($theme !== '' && !in_array($themeKey, ['regional', 'desarrolloregional'], true)) {
            return trim(preg_replace('/^regi[oó]n\s+/iu', '', $theme));
        }

        if (preg_match('/regi[oó]n\s+(.+)$/iu', $indicatorName, $matches)) {
            return trim(preg_replace('/^regi[oó]n\s+/iu', '', $matches[1]));
        }

        return $theme;
    }

    private function resolveInstitution(string $name): ?Institucion
    {
        $sourceKey = $this->key($name);
        if ($sourceKey === '') {
            return null;
        }

        $institutions = Institucion::query()->orderBy('id')->get(['id', 'nombre']);

        foreach ($institutions as $institution) {
            if ($this->key($institution->nombre) === $sourceKey) {
                return $institution;
            }
        }

        foreach ($institutions as $institution) {
            $institutionKey = $this->key($institution->nombre);
            if (str_contains($institutionKey, $sourceKey) || str_contains($sourceKey, $institutionKey)) {
                return $institution;
            }
        }

        return null;
    }

    private function normalizeTrend($value): string
    {
        $key = $this->key((string) $value);

        return match ($key) {
            'mayoresmejor' => 'Mayor es Mejor',
            'menoresmejor' => 'Menor es Mejor',
            'constante' => 'Constante',
            'ascendente' => 'Mayor es Mejor',
            default => 'No definida',
        };
    }

    private function recordFieldIssue(array &$issues, string $field, $value, array $row): void
    {
        if (!$this->isMissingText($value)) {
            return;
        }

        $issues[] = [
            'source_row' => $row['source_row'],
            'indicator' => $row['name'],
            'type' => $row['type'],
            'program' => $row['parent_name'],
            'value' => trim((string) $value) ?: 'Vacio',
        ];
    }

    private function numeric($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || in_array($this->key($value), ['nd', 'na', 'n/a'], true)) {
            return null;
        }

        $value = str_replace([',', '$', '%'], '', $value);
        return is_numeric($value) ? $value : null;
    }

    private function date($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        foreach (['m/d/Y', 'n/j/Y', 'd/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            } catch (Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    private function requiredText($value): string
    {
        return $this->nullableText($value) ?: 'N/D';
    }

    private function nullableText($value): ?string
    {
        $value = trim((string) $value);
        return $value === '' || in_array($this->key($value), ['nd', 'na', 'n/a', 'null'], true) ? null : $value;
    }

    private function isMissingText($value): bool
    {
        return $this->nullableText($value) === null;
    }

    private function text($value): string
    {
        return trim((string) $value);
    }

    private function key(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(
            class_exists('Normalizer')
                ? preg_replace('/\p{Mn}/u', '', \Normalizer::normalize($value, \Normalizer::FORM_D))
                : $value
        ));
    }
}
