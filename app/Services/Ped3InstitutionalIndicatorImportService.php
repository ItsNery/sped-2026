<?php

namespace App\Services;

use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\DatoAnual;
use App\Models\Indicador;
use App\Models\Institucion;
use App\Models\Odses;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class Ped3InstitutionalIndicatorImportService
{
    private const PLAN_ID = 3;

    /**
     * @return array{created: int, updated: int, rows: int, errors: list<string>}
     */
    public function run(string $filePath, bool $execute): array
    {
        if (!is_file($filePath)) {
            throw new RuntimeException("No se encontro el archivo: {$filePath}");
        }

        $rows = $this->readRows($filePath);
        $operations = [];
        $errors = [];

        foreach ($rows as $rowNumber => $row) {
            try {
                $operations[] = $this->resolveRow($row);
            } catch (RuntimeException $exception) {
                $errors[] = "Fila {$rowNumber}: {$exception->getMessage()}";
            }
        }

        $result = [
            'created' => count(array_filter($operations, fn (array $operation) => $operation['indicator'] === null)),
            'updated' => count(array_filter($operations, fn (array $operation) => $operation['indicator'] !== null)),
            'rows' => count($rows),
            'errors' => $errors,
        ];

        if (!$execute || $errors) {
            return $result;
        }

        DB::transaction(function () use ($operations): void {
            foreach ($operations as $operation) {
                /** @var Indicador $indicator */
                $indicator = $operation['indicator'] ?? new Indicador();
                $indicator->fill($operation['attributes']);
                $indicator->save();

                $indicator->programasInstitucionales()->syncWithoutDetaching([$operation['program']->id]);
                $indicator->ods()->sync($operation['ods']);

                foreach ($operation['annualValues'] as $year => $value) {
                    DatoAnual::withoutEvents(function () use ($indicator, $year, $value): void {
                        DatoAnual::updateOrCreate(
                            ['id_indicador' => $indicator->id, 'anio' => $year],
                            ['valor_dato' => $value, 'validado' => true, 'modificado' => false],
                        );
                    });
                }
            }
        });

        return $result;
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function readRows(string $filePath): array
    {
        $rawRows = IOFactory::load($filePath)->getActiveSheet()->toArray(null, true, true, false);
        $header = array_shift($rawRows);

        if ($header === null) {
            throw new RuntimeException('El archivo esta vacio.');
        }

        $columns = [];
        foreach ($header as $index => $value) {
            $columns[$this->key((string) $value)] = $index;
        }

        foreach (['nombreindicador', 'programaderivado', 'programa', 'tematica', 'lineabaseano', 'lineabasedato', 'unidaddemedida', 'meta2030', 'periodicidad', 'tendencia', 'formula', 'institucion'] as $required) {
            if (!array_key_exists($required, $columns)) {
                throw new RuntimeException("Falta la columna requerida: {$required}.");
            }
        }

        $rows = [];
        foreach ($rawRows as $index => $rawRow) {
            $row = [];
            foreach ($columns as $name => $columnIndex) {
                $row[$name] = $rawRow[$columnIndex] ?? null;
            }

            if (count(array_filter($row, fn ($value) => trim((string) $value) !== '')) > 0) {
                $rows[$index + 2] = $row;
            }
        }

        if (!$rows) {
            throw new RuntimeException('El archivo no contiene indicadores.');
        }

        return $rows;
    }

    /**
     * @param array<string, string|null> $row
     * @return array{indicator: ?Indicador, program: CatProgramaDerivadoInstitucional, attributes: array<string, mixed>, ods: list<int>, annualValues: array<int, string>}
     */
    private function resolveRow(array $row): array
    {
        $name = $this->requiredText($row['nombreindicador'] ?? null, 'Nombre Indicador');
        $program = $this->program($this->requiredText($row['programaderivado'] ?? null, 'Programa Derivado'));
        $institution = $this->institution($this->requiredText($row['institucion'] ?? null, 'Institucion'));
        $annualValues = $this->annualValues($row);
        $baselineYear = (int) $this->requiredText($row['lineabaseano'] ?? null, 'Linea Base (Año)');
        $baselineValue = $this->numeric($row['lineabasedato'] ?? null, 'Linea Base (Dato)');
        $annualValues[$baselineYear] = $baselineValue;
        ksort($annualValues);

        $indicators = Indicador::query()
            ->where('nombre', $name)
            ->whereHas('programasInstitucionales', fn ($query) => $query->whereKey($program->id))
            ->get();

        if ($indicators->count() > 1) {
            throw new RuntimeException("El indicador '{$name}' es ambiguo en '{$program->nombre}'.");
        }

        return [
            'indicator' => $indicators->first(),
            'program' => $program,
            'attributes' => [
                'nombre' => $name,
                'programa_derivado' => $program->nombre,
                'programa' => $this->requiredText($row['programa'] ?? null, 'Programa'),
                'cod_tematica' => '',
                'tematica' => $this->requiredText($row['tematica'] ?? null, 'Temática'),
                'id_institucion' => $institution->id,
                'linea_base' => $baselineYear,
                'dato_linea_base' => $baselineValue,
                'meta_anio' => 2030,
                'meta' => $this->numeric($row['meta2030'] ?? null, 'Meta 2030'),
                'unidad_medida' => $this->requiredText($row['unidaddemedida'] ?? null, 'Unidad de Medida'),
                'fuente' => $this->text($row['fuente'] ?? null),
                'liga' => $this->text($row['enlace'] ?? null),
                'descripcion' => $this->text($row['descripcion'] ?? null),
                'periodicidad' => $this->requiredText($row['periodicidad'] ?? null, 'Periodicidad'),
                'cobertura' => $this->text($row['cobertura'] ?? null) ?: 'N/D',
                'tendencia' => $this->requiredText($row['tendencia'] ?? null, 'Tendencia'),
                'resultados' => $this->text($row['resultadosgenerales'] ?? null),
                'formula' => $this->requiredText($row['formula'] ?? null, 'Fórmula'),
                'fecha_actualizacion' => $this->date($row['fechaactualizacionindicador'] ?? null),
                'indicador_validado' => true,
                'indicadorable_type' => CatProgramaDerivadoInstitucional::class,
                'indicadorable_id' => $program->id,
            ],
            'ods' => $this->ods($row['ods'] ?? null),
            'annualValues' => $annualValues,
        ];
    }

    private function program(string $name): CatProgramaDerivadoInstitucional
    {
        $programs = CatProgramaDerivadoInstitucional::where('plan_estatal', self::PLAN_ID)->get();
        $program = $programs->first(fn (CatProgramaDerivadoInstitucional $item) => $this->key($item->nombre) === $this->key($name));

        if (!$program) {
            throw new RuntimeException("No existe el programa institucional '{$name}' en PED 3.");
        }

        return $program;
    }

    private function institution(string $name): Institucion
    {
        $institutions = Institucion::query()->get();
        $institution = $institutions->first(fn (Institucion $item) => $this->key($item->nombre) === $this->key($name));

        if (!$institution) {
            throw new RuntimeException("No existe la institución '{$name}'.");
        }

        return $institution;
    }

    /**
     * @param array<string, string|null> $row
     * @return array<int, string>
     */
    private function annualValues(array $row): array
    {
        $values = [];

        foreach ($row as $header => $value) {
            if (preg_match('/^dato(20\d{2})$/', $header, $matches) && $this->text($value) !== '') {
                $values[(int) $matches[1]] = $this->numeric($value, "Dato {$matches[1]}");
            }
        }

        return $values;
    }

    /**
     * @return list<int>
     */
    private function ods(?string $value): array
    {
        $ids = array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $this->text($value)))));

        if (array_filter($ids, fn (string $id) => !ctype_digit($id))) {
            throw new RuntimeException('ODS debe contener IDs numéricos separados por comas.');
        }

        $ids = array_map('intval', array_unique($ids));
        if (count($ids) !== Odses::whereIn('id', $ids)->count()) {
            throw new RuntimeException('Uno o más ODS no existen en el catálogo.');
        }

        return $ids;
    }

    private function requiredText(?string $value, string $label): string
    {
        $value = $this->text($value);

        if ($value === '') {
            throw new RuntimeException("{$label} es obligatorio.");
        }

        return $value;
    }

    private function numeric(?string $value, string $label): string
    {
        $value = str_replace([',', '$', '%'], '', $this->text($value));

        if (!is_numeric($value)) {
            throw new RuntimeException("{$label} debe ser numérico.");
        }

        return $value;
    }

    private function date(?string $value): string
    {
        $value = $this->text($value);

        if ($value === '') {
            return now()->toDateString();
        }

        if (is_numeric($value) && (float) $value > 20_000) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        return Carbon::parse($value)->toDateString();
    }

    private function text(?string $value): string
    {
        return trim((string) $value);
    }

    private function key(string $value): string
    {
        if (class_exists('Normalizer')) {
            $value = \Normalizer::normalize($value, \Normalizer::FORM_D) ?: $value;
            $value = preg_replace('/\p{Mn}/u', '', $value) ?: $value;
        } else {
            $value = strtr($value, [
                'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
                'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
            ]);
        }

        return preg_replace('/[^a-z0-9]/', '', mb_strtolower($value)) ?: '';
    }
}
