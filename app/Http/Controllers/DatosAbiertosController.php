<?php

namespace App\Http\Controllers;

use App\Models\CatMunicipio;
use App\Models\MunicipioConvenio;
use App\Models\IndicadorMunicipal;
use App\Models\ResultadoIndicadorMunicipal;
use App\Models\IndicadorMunicipalODS;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Cache;

class DatosAbiertosController extends Controller
{
    public function municipiosIndicadores()
    {
        // 1. Obtener SOLO los MunicipioConvenio cuyo municipio relacionado TENGA indicadores
        $municipios = Cache::remember('datos_abiertos:municipios', now()->addMinutes(10), function () {
            return MunicipioConvenio::with('municipio')
                ->whereHas('municipio', function ($queryMunicipio) {
                    $queryMunicipio->has('indicadores');
                })
                ->get();
        });

        // 2. Iterar sobre la colección FILTRADA y añadir el campo 'slug'
        $municipios->each(function ($municipioConvenio) {
            if ($municipioConvenio->municipio && $municipioConvenio->municipio->nombre) {
                $municipioConvenio->slug = Str::slug($municipioConvenio->municipio->nombre);
            } else {
                $municipioConvenio->slug = 'sin-nombre-o-datos-' . $municipioConvenio->id;
            }
        });

        // 3. Pasar la colección MODIFICADA y FILTRADA a la vista
        return view('datos-abiertos-mun', compact('municipios'));
    }

    /**
     * Procesa la solicitud de descarga de datos para un municipio específico y formato.
     * Incorpora subconsultas para obtener datos anuales directamente.
     *
     * @param int $municipioId El ID del Municipio.
     * @param string $formato El formato solicitado ('json', 'csv', 'xlsx').
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function descargarDatosMunicipio($municipioId, $formato)
    {
        // 1. Buscar el Municipio para obtener su nombre y validar existencia
        $municipio = CatMunicipio::find($municipioId);

        if (!$municipio) {
            abort(404, 'Municipio no encontrado.');
        }

        // --- Inicio de la Consulta Mejorada con Subconsultas ---

        // 2. Define los años que necesitas (desde 2019 hasta el año actual)
        $startYear = 2019;
        $currentYear = Carbon::now()->year;
        $years = range($startYear, $currentYear);

        // 3. Construye la consulta base seleccionando campos del IndicadorMunicipal
        $query = IndicadorMunicipal::where('id_municipio', $municipioId)->where('publica', '1')
            ->select([
                'id',
                'indicador',
                'instrumento',
                'eje_indicador',
                'tematica',
                'descripcion',
                'unidad_medida',
                'linea_base',
                'dato_linea',
                'meta_2024',
                'fuente',
                'liga',
                'periodicidad_id',
                'cobertura',
                'tendencia',
                'id_tipo',
                'id_nivel',
                'id_dimension',
                'dependencia',
                'formula',
                'id_municipio',
                'created_at',
                'updated_at',
                'proxima_actualizacion'
            ]);

        // 4. Añade subconsultas dinámicas para cada año y cada campo (dato/resultado)
        foreach ($years as $year) {
            $query->addSelect([
                "dato_{$year}" => ResultadoIndicadorMunicipal::select('dato')
                    ->whereColumn('id_indicador', 'indicadores_municipales.id')
                    ->where('año', $year)
                    ->orderBy('periodo', 'desc')
                    ->limit(1)
            ]);

            $query->addSelect([
                "resultado_{$year}" => ResultadoIndicadorMunicipal::select('resultado')
                    ->whereColumn('id_indicador', 'indicadores_municipales.id')
                    ->where('año', $year)
                    ->orderBy('periodo', 'desc')
                    ->limit(1)
            ]);
        }
        $tablaIndicadores = (new IndicadorMunicipal)->getTable();
        $tablaOdsLink = (new IndicadorMunicipalODS)->getTable();

        $query->addSelect([
            'ods1' => IndicadorMunicipalODS::select('id_ods')
                ->whereColumn("{$tablaOdsLink}.id_indicador", "{$tablaIndicadores}.id")
                ->orderBy('id_ods', 'asc')
                ->limit(1)
        ]);

        $query->addSelect([
            'ods2' => IndicadorMunicipalODS::select('id_ods')
                ->whereColumn("{$tablaOdsLink}.id_indicador", "{$tablaIndicadores}.id")
                ->orderBy('id_ods', 'asc')
                ->offset(1)
                ->limit(1)
        ]);

        $query->addSelect([
            'ods3' => IndicadorMunicipalODS::select('id_ods')
                ->whereColumn("{$tablaOdsLink}.id_indicador", "{$tablaIndicadores}.id")
                ->orderBy('id_ods', 'asc')
                ->offset(2)
                ->limit(1)
        ]);

        // 5. Ejecuta la consulta final
        $datos = $query->with([
            'periodicidad',
            'tipo',
            'nivel',
            'dimension',
            'municipio'
        ])->orderBy('indicador')->get();


        // 6. Verifica si se encontraron indicadores (la consulta principal)
        if ($datos->isEmpty()) {
            return redirect()->back()->with('warning', 'No hay indicadores definidos para este municipio.');
        }

        // 7. Prepara nombre base para el archivo de descarga
        $fechaActual = Carbon::now()->format('Ymd');
        $nombreBaseArchivo = Str::slug($municipio->nombre) . "_datos_{$fechaActual}";

        // 8. Intenta generar y devolver la respuesta según el formato
        try {
            switch ($formato) {
                case 'xlsx':
                    return $this->generarExcel($datos, $nombreBaseArchivo . '.xlsx', $municipio->nombre, $years);
                case 'csv':
                    return $this->generarCsv($datos, $nombreBaseArchivo . '.csv', $municipio->nombre, $years);
                case 'json':
                    return Response::json($datos, 200, [
                        'Content-Disposition' => 'attachment; filename="' . $nombreBaseArchivo . '.json"',
                        'Content-Type' => 'application/json',
                    ]);
                default:
                    abort(400, 'Formato no válido solicitado.');
            }
        } catch (\Exception $e) {
            Log::error("Error generando descarga $formato para municipio $municipioId: " . $e->getMessage(), ['exception' => $e]);
            abort(500, 'Error interno al generar el archivo de descarga. Por favor, intente más tarde.');
        }
    }

    /**
     * Función auxiliar para generar la respuesta Excel (similar a ejemplos anteriores).
     */
    /**
     * Función auxiliar para generar la respuesta Excel.
     * Puede usar una plantilla o generar desde cero.
     * Adaptada para recibir datos con columnas anuales (dato_YYYY, resultado_YYYY).
     *
     * @param \Illuminate\Support\Collection $datos La colección de datos a exportar.
     * @param string $nombreArchivo El nombre deseado para el archivo descargado.
     * @param string $nombreMunicipio El nombre del municipio (para posible uso en la plantilla/hoja).
     * @param array $years El array de años (ej. [2019, 2020, ..., 2025]) para las columnas dinámicas.
     * @return StreamedResponse
     * @throws \Exception Si la plantilla no se encuentra o hay error al leerla.
     */
    private function generarExcel($datos, $nombreArchivo, $nombreMunicipio, $years)
    {
        // --- OPCIÓN 1: Usando Plantilla Excel ---
        $templatePath = storage_path('app/plantillas/plantilla-mun.xlsx');

        if (!file_exists($templatePath)) {
            throw new \Exception("Plantilla Excel no encontrada en {$templatePath}");
        }

        try {
            $spreadsheet = IOFactory::load($templatePath);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            throw new \Exception("Error al leer la plantilla Excel: " . $e->getMessage());
        }

        $sheet = $spreadsheet->getActiveSheet();

        $filaInicioTabla = 2;
        $filaActual = $filaInicioTabla;
        foreach ($datos as $dato) {
            $sheet->setCellValue('A' . $filaActual, $dato->id);
            $sheet->setCellValue('B' . $filaActual, $dato->indicador);
            $sheet->setCellValue('C' . $filaActual, $dato->instrumento);
            $sheet->setCellValue('D' . $filaActual, $dato->eje_indicador);
            $sheet->setCellValue('E' . $filaActual, $dato->tematica);
            $sheet->setCellValue('F' . $filaActual, $dato->descripcion);
            $sheet->setCellValue('G' . $filaActual, $dato->unidad_medida);
            $sheet->setCellValue('H' . $filaActual, $dato->linea_base);
            $sheet->setCellValue('I' . $filaActual, $dato->dato_linea);
            $sheet->setCellValue('J' . $filaActual, $dato->meta_2024);
            $sheet->setCellValue('K' . $filaActual, $dato->fuente);
            $sheet->setCellValue('L' . $filaActual, $dato->liga);
            $sheet->setCellValue('M' . $filaActual, $dato->periodicidad->nombre);
            $sheet->setCellValue('N' . $filaActual, $dato->cobertura);
            $sheet->setCellValue('O' . $filaActual, $dato->tendencia);
            $sheet->setCellValue('P' . $filaActual, $dato->tipo->nombre);
            $sheet->setCellValue('Q' . $filaActual, $dato->nivel->nombre);
            $sheet->setCellValue('R' . $filaActual, $dato->dimension->nombre);
            $sheet->setCellValue('S' . $filaActual, $dato->dependencia);
            $sheet->setCellValue('T' . $filaActual, $dato->formula);
            $sheet->setCellValue('U' . $filaActual, $dato->created_at);
            $sheet->setCellValue('V' . $filaActual, $dato->updated_at);
            $sheet->setCellValue('W' . $filaActual, $dato->proxima_actualizacion);
            // $sheet->setCellValue('X' . $filaActual, $dato->municipio->nombre);
            $sheet->setCellValue('X' . $filaActual, $nombreMunicipio);
            $sheet->setCellValue('Y' . $filaActual, $dato->ods1 ?? 'N/A');
            $sheet->setCellValue('Z' . $filaActual, $dato->ods2 ?? 'N/A');
            $sheet->setCellValue('AA' . $filaActual, $dato->ods3 ?? 'N/A');


            // Mapea columnas anuales dinámicas
            $colIndexLetra = 'AB';
            foreach ($years as $year) {
                $datoCol = "dato_{$year}";
                $resultadoCol = "resultado_{$year}";

                $sheet->setCellValue($colIndexLetra . $filaActual, $dato->$datoCol);
                $colIndexLetra++;

                $sheet->setCellValue($colIndexLetra . $filaActual, $dato->$resultadoCol);
                $colIndexLetra++;
            }
            $filaActual++;
        }
        // --- Fin Escritura en Plantilla ---

        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $nombreArchivo . '"');
        $response->headers->set('Cache-Control', 'max-age=0');
        $response->headers->set('Pragma', 'public'); 
        return $response;
    }

    /**
     * Función auxiliar para generar la respuesta CSV.
     * Adaptada para coincidir con la estructura de columnas del Excel generado.
     *
     * @param \Illuminate\Support\Collection $datos La colección de datos a exportar (ya debe incluir datos anuales y ODS).
     * @param string $nombreArchivo El nombre deseado para el archivo descargado.
     * @param string $nombreMunicipio El nombre del municipio.
     * @param array $years El array de años (ej. [2019, 2020, ..., 2025]) para las columnas dinámicas.
     * @return StreamedResponse
     */
    private function generarCsv($datos, $nombreArchivo, $nombreMunicipio, $years)
    {
        if ($datos->isEmpty()) {
            return Response::make('', 204);
        }

        $cabecerasCsv = [
            'ID',                       // A: $dato->id
            'Indicador',                // B: $dato->indicador
            'Instrumento',              // C: $dato->instrumento
            'Eje Indicador',            // D: $dato->eje_indicador
            'Tematica',                 // E: $dato->tematica
            'Descripcion',              // F: $dato->descripcion
            'Unidad Medida',            // G: $dato->unidad_medida
            'Linea Base (Año)',         // H: $dato->linea_base
            'Linea Base (Dato)',        // I: $dato->dato_linea
            'Meta 2024',                // J: $dato->meta_2024 (Corregido)
            'Fuente',                   // K: $dato->fuente
            'Liga Fuente',              // L: $dato->liga
            'Periodicidad',             // M: $dato->periodicidad->nombre
            'Cobertura',                // N: $dato->cobertura
            'Tendencia',                // O: $dato->tendencia
            'Tipo',                     // P: $dato->tipo->nombre
            'Nivel',                    // Q: $dato->nivel->nombre
            'Dimension',                // R: $dato->dimension->nombre
            'Dependencia Responsable',  // S: $dato->dependencia
            'Formula',                  // T: $dato->formula
            'Fecha Creacion Indicador', // U: $dato->created_at
            'Fecha Actualizacion Indicador', // V: $dato->updated_at
            'Proxima Actualizacion',    // W: $dato->proxima_actualizacion
            'Municipio',                // X: $nombreMunicipio (variable pasada)
            'ODS 1 ID',                 // Y: $dato->ods1
            'ODS 2 ID',                 // Z: $dato->ods2
            'ODS 3 ID',                 // AA: $dato->ods3
        ];

        foreach ($years as $year) {
            $cabecerasCsv[] = "Dato {$year}";       // AB, AD, AF, ...
            $cabecerasCsv[] = "Resultado {$year}";  // AC, AE, AG, ...
        }

        $headersHttp = [
            'Content-Type'              => 'text/csv; charset=utf-8',
            'Content-Disposition'       => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'                    => 'public',
            'Cache-Control'             => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'                   => '0',
        ];

        $callback = function () use ($datos, $cabecerasCsv, $nombreMunicipio, $years) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, $cabecerasCsv);

            foreach ($datos as $dato) {
                $filaParaCsv = [
                    $dato->id ?? '',                            // A
                    $dato->indicador ?? '',                     // B
                    $dato->instrumento ?? '',                  // C
                    $dato->eje_indicador ?? '',                // D
                    $dato->tematica ?? '',                     // E
                    $dato->descripcion ?? '',                  // F
                    $dato->unidad_medida ?? '',                // G
                    $dato->linea_base ?? '',                   // H
                    $dato->dato_linea ?? '',                   // I
                    $dato->meta_2024 ?? '',                    // J
                    $dato->fuente ?? '',                       // K
                    $dato->liga ?? '',                         // L
                    $dato->periodicidad->nombre ?? '',         // M - Accede a relación cargada
                    $dato->cobertura ?? '',                    // N
                    $dato->tendencia ?? '',                    // O
                    $dato->tipo->nombre ?? '',                 // P - Accede a relación cargada
                    $dato->nivel->nombre ?? '',                // Q - Accede a relación cargada
                    $dato->dimension->nombre ?? '',            // R - Accede a relación cargada
                    $dato->dependencia ?? '',                  // S
                    $dato->formula ?? '',                      // T
                    $dato->created_at ? $dato->created_at->format('Y-m-d H:i:s') : '', // U
                    $dato->updated_at ? $dato->updated_at->format('Y-m-d H:i:s') : '', // V
                    $dato->proxima_actualizacion ?? '',        // W
                    $nombreMunicipio,                          // X
                    $dato->ods1 ?? '',                         // Y 
                    $dato->ods2 ?? '',                         // Z
                    $dato->ods3 ?? '',                         // AA
                ];

                foreach ($years as $year) {
                    $datoCol = "dato_{$year}";
                    $resultadoCol = "resultado_{$year}";
                    $filaParaCsv[] = $dato->$datoCol ?? '';
                    $filaParaCsv[] = $dato->$resultadoCol ?? '';
                }
                fputcsv($file, $filaParaCsv);
            }
            fclose($file);
        };
        return Response::stream($callback, 200, $headersHttp);
    }
}
