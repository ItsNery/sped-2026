<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class CandidateExcelReconciliationService
{
    /**
     * @return array{generated_at: string, tables: array<string, array<string, mixed>>}
     */
    public function reconcile(string $indicatorPath, string $institutionPath): array
    {
        $indicatorSource = $this->readRows($indicatorPath, $this->indicatorColumns());
        $institutionSource = $this->readRows($institutionPath, $this->institutionColumns());

        return [
            'generated_at' => now()->toIso8601String(),
            'tables' => [
                'indicadors' => $this->compareRows(
                    $indicatorSource,
                    'indicadors',
                    $this->indicatorColumns(),
                    'nombre',
                ),
                'instituciones' => $this->compareRows(
                    $institutionSource,
                    'instituciones',
                    $this->institutionColumns(),
                    'nombre',
                ),
            ],
        ];
    }

    /**
     * @return array{generated_at: string, mode: string, tables: array<string, array<string, mixed>>}
     */
    public function apply(string $indicatorPath, string $institutionPath, bool $execute): array
    {
        $indicatorSource = $this->readRows($indicatorPath, $this->indicatorColumns());
        $institutionSource = $this->readRows($institutionPath, $this->institutionColumns());
        $institutionDatabase = $this->databaseRows('instituciones', $this->institutionColumns());
        $indicatorDatabase = $this->databaseRows('indicadors', $this->indicatorColumns());

        $tables = [
            'instituciones' => $this->compareRows($institutionSource, 'instituciones', $this->institutionColumns(), 'nombre', $institutionDatabase),
            'indicadors' => $this->compareRows($indicatorSource, 'indicadors', $this->indicatorColumns(), 'nombre', $indicatorDatabase),
        ];
        $institutionOperations = $this->operations($institutionSource, $institutionDatabase, $this->institutionColumns());
        $indicatorOperations = $this->operations($indicatorSource, $indicatorDatabase, $this->indicatorColumns());

        $this->validateReferences($indicatorOperations, $indicatorSource, $institutionSource, $institutionDatabase);

        if ($execute) {
            DB::transaction(function () use ($institutionOperations, $indicatorOperations, $institutionSource, $indicatorSource): void {
                $this->applyRows('instituciones', $institutionOperations, $institutionSource, $this->institutionColumns());
                $this->applyRows('indicadors', $indicatorOperations, $indicatorSource, $this->indicatorColumns());
            });
        }

        return [
            'generated_at' => now()->toIso8601String(),
            'mode' => $execute ? 'executed' : 'dry-run',
            'tables' => $tables,
        ];
    }

    /**
     * @param list<string> $columns
     * @return array<string, mixed>
     */
    private function compareRows(array $source, string $table, array $columns, string $labelColumn, ?array $database = null): array
    {
        $database ??= $this->databaseRows($table, $columns);

        $newInExcel = [];
        $changed = [];

        foreach ($source as $id => $sourceRow) {
            if (!array_key_exists($id, $database)) {
                $newInExcel[] = $this->summary($sourceRow, $labelColumn);

                continue;
            }

            $changes = $this->changes($sourceRow, $database[$id], $columns);
            if ($changes) {
                $changed[] = [
                    ...$this->summary($sourceRow, $labelColumn),
                    'changes' => $changes,
                ];
            }
        }

        $missingInExcel = [];
        foreach ($database as $id => $databaseRow) {
            if (!array_key_exists($id, $source)) {
                $missingInExcel[] = $this->summary($databaseRow, $labelColumn);
            }
        }

        return [
            'source_rows' => count($source),
            'database_rows' => count($database),
            'new_in_excel_count' => count($newInExcel),
            'new_in_excel' => $newInExcel,
            'missing_in_excel_count' => count($missingInExcel),
            'missing_in_excel' => $missingInExcel,
            'changed_count' => count($changed),
            'changed' => $changed,
            'unchanged_count' => count($source) - count($newInExcel) - count($changed),
        ];
    }

    /**
     * @param list<string> $columns
     * @return array<string, array<string, mixed>>
     */
    private function databaseRows(string $table, array $columns): array
    {
        return DB::table($table)->select(['id', ...$columns])->get()->mapWithKeys(
            fn (object $row): array => [(string) $row->id => (array) $row],
        )->all();
    }

    /**
     * @param list<string> $columns
     * @return array{create: list<string>, update: list<string>}
     */
    private function operations(array $source, array $database, array $columns): array
    {
        $operations = ['create' => [], 'update' => []];

        foreach ($source as $id => $sourceRow) {
            if (!array_key_exists($id, $database)) {
                $operations['create'][] = $id;
            } elseif ($this->changes($sourceRow, $database[$id], $columns)) {
                $operations['update'][] = $id;
            }
        }

        return $operations;
    }

    /**
     * @param array{create: list<string>, update: list<string>} $operations
     * @param array<string, array<string, mixed>> $source
     * @param list<string> $columns
     */
    private function applyRows(string $table, array $operations, array $source, array $columns): void
    {
        foreach ($operations['create'] as $id) {
            DB::table($table)->insert([
                'id' => (int) $id,
                ...$this->attributes($source[$id], $columns),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($operations['update'] as $id) {
            DB::table($table)->where('id', (int) $id)->update([
                ...$this->attributes($source[$id], $columns),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * @param array{create: list<string>, update: list<string>} $indicatorOperations
     * @param array<string, array<string, mixed>> $indicatorSource
     * @param array<string, array<string, mixed>> $institutionSource
     * @param array<string, array<string, mixed>> $institutionDatabase
     */
    private function validateReferences(array $indicatorOperations, array $indicatorSource, array $institutionSource, array $institutionDatabase): void
    {
        $indicatorIds = [...$indicatorOperations['create'], ...$indicatorOperations['update']];
        $institutionIds = [];
        $userIds = [];

        foreach ($indicatorIds as $id) {
            $institutionId = $this->normalize($indicatorSource[$id]['id_institucion'] ?? null, 'id_institucion');
            $userId = $this->normalize($indicatorSource[$id]['id_usuario'] ?? null, 'id_usuario');

            if ($institutionId !== null) {
                $institutionIds[] = $institutionId;
            }

            if ($userId !== null) {
                $userIds[] = $userId;
            }
        }

        $availableInstitutions = array_fill_keys([...array_keys($institutionDatabase), ...array_keys($institutionSource)], true);
        foreach (array_unique($institutionIds) as $institutionId) {
            if (!isset($availableInstitutions[$institutionId])) {
                throw new RuntimeException("No existe la institucion con ID {$institutionId} requerida por los indicadores.");
            }
        }

        if ($userIds) {
            $availableUsers = DB::table('users')->whereIn('id', array_unique(array_map('intval', $userIds)))->pluck('id')->map(
                fn (int $id): string => (string) $id,
            )->all();
            $missingUsers = array_diff(array_unique($userIds), $availableUsers);

            if ($missingUsers) {
                throw new RuntimeException('No existen los usuarios requeridos por los indicadores: ' . implode(', ', $missingUsers) . '.');
            }
        }
    }

    /**
     * @param list<string> $columns
     * @return array<string, string|null>
     */
    private function attributes(array $row, array $columns): array
    {
        $attributes = [];
        foreach ($columns as $column) {
            $attributes[$column] = $this->writeValue($row[$column] ?? null, $column);
        }

        return $attributes;
    }

    private function writeValue(mixed $value, string $column): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if ($column === 'fecha_actualizacion') {
            return $this->normalize($value, $column);
        }

        return trim((string) $value);
    }

    /**
     * @return list<string>
     */
    private function indicatorColumns(): array
    {
        return [
            'nombre', 'slug', 'programa_derivado', 'programa', 'cod_tematica', 'tematica',
            'id_institucion', 'linea_base', 'dato_linea_base', 'meta_2024', 'meta_anio', 'meta',
            'unidad_medida', 'id_usuario', 'fuente', 'liga', 'descripcion', 'periodicidad', 'periodo',
            'cobertura', 'tendencia', 'fecha_actualizacion', 'resultados', 'formula',
            'indicador_validado', 'indicadorable_type', 'indicadorable_id',
        ];
    }

    /**
     * @return list<string>
     */
    private function institutionColumns(): array
    {
        return ['nombre', 'titular'];
    }

    /**
     * @param list<string> $columns
     * @return array<string, array{excel: string|null, database: string|null}>
     */
    private function changes(array $sourceRow, array $databaseRow, array $columns): array
    {
        $changes = [];

        foreach ($columns as $column) {
            $sourceValue = $this->normalize($sourceRow[$column] ?? null, $column);
            $databaseValue = $this->normalize($databaseRow[$column] ?? null, $column);

            if ($sourceValue !== $databaseValue) {
                $changes[$column] = [
                    'excel' => $sourceValue,
                    'database' => $databaseValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * @param list<string> $columns
     * @return array<string, array<string, mixed>>
     */
    private function readRows(string $filePath, array $columns): array
    {
        if (!is_file($filePath)) {
            throw new RuntimeException("No se encontro el archivo: {$filePath}");
        }

        try {
            $rows = IOFactory::load($filePath)->getActiveSheet()->toArray(null, true, true, false);
        } catch (\Throwable $exception) {
            throw new RuntimeException("No se pudo leer el archivo {$filePath}: {$exception->getMessage()}", previous: $exception);
        }

        $header = array_shift($rows);
        if ($header === null) {
            throw new RuntimeException("El archivo {$filePath} esta vacio.");
        }

        $headerColumns = [];
        foreach ($header as $index => $column) {
            $headerColumns[trim((string) $column)] = $index;
        }

        foreach (['id', ...$columns] as $column) {
            if (!array_key_exists($column, $headerColumns)) {
                throw new RuntimeException("El archivo {$filePath} no contiene la columna requerida: {$column}.");
            }
        }

        $source = [];
        foreach ($rows as $rowNumber => $row) {
            $id = $this->normalize($row[$headerColumns['id']] ?? null, 'id');
            if ($id === null) {
                continue;
            }

            if (!ctype_digit($id)) {
                throw new RuntimeException("El archivo {$filePath} tiene un ID invalido en la fila " . ($rowNumber + 2) . '.');
            }

            if (array_key_exists($id, $source)) {
                throw new RuntimeException("El archivo {$filePath} tiene el ID duplicado {$id}.");
            }

            $source[$id] = ['id' => $id];
            foreach ($columns as $column) {
                $source[$id][$column] = $row[$headerColumns[$column]] ?? null;
            }
        }

        return $source;
    }

    /**
     * @return array{id: string, label: string|null}
     */
    private function summary(array $row, string $labelColumn): array
    {
        return [
            'id' => (string) $row['id'],
            'label' => $this->normalize($row[$labelColumn] ?? null, $labelColumn),
        ];
    }

    private function normalize(mixed $value, string $column): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if ($column === 'fecha_actualizacion') {
            try {
                return is_numeric($value) && (float) $value > 20_000
                    ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d')
                    : Carbon::parse((string) $value)->toDateString();
            } catch (\Throwable) {
                return trim((string) $value);
            }
        }

        if (is_numeric($value)) {
            $normalized = rtrim(rtrim(number_format((float) $value, 8, '.', ''), '0'), '.');

            return $normalized === '' ? '0' : $normalized;
        }

        return trim((string) $value);
    }
}
