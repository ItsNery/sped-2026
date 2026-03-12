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

class DatosAbiertosController extends Controller
{
    public function municipiosIndicadores()
    {
        // 1. Obtener SOLO los MunicipioConvenio cuyo municipio relacionado TENGA indicadores
        $municipios = MunicipioConvenio::with('municipio') // Carga ansiosa para eficiencia
            ->whereHas('municipio', function ($queryMunicipio) {
                // $queryMunicipio es una instancia del Query Builder para la relación 'municipio' (CatMunicipios)
                // Ahora verificamos si este CatMunicipios tiene registros relacionados en 'indicadores'
                $queryMunicipio->has('indicadores'); // 'indicadores' es el nombre de la relación definida en CatMunicipios
            })
            ->get(); // Ejecuta la consulta y obtén la colección filtrada

        // 2. Iterar sobre la colección FILTRADA y añadir el campo 'slug'
        $municipios->each(function ($municipioConvenio) {
            // La comprobación if sigue siendo útil por si 'nombre' fuera null
            if ($municipioConvenio->municipio && $municipioConvenio->municipio->nombre) {
                $municipioConvenio->slug = Str::slug($municipioConvenio->municipio->nombre);
            } else {
                // Este caso es ahora menos probable, pero se mantiene por seguridad
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
            // Puedes usar abort() o redirigir como prefieras
            // return redirect()->route('ruta.principal')->with('error', 'Municipio no encontrado.');
            abort(404, 'Municipio no encontrado.');
        }

        // --- Inicio de la Consulta Mejorada con Subconsultas ---

        // 2. Define los años que necesitas (desde 2019 hasta el año actual)
        $startYear = 2019;
        $currentYear = Carbon::now()->year; // Obtiene el año actual
        $years = range($startYear, $currentYear);

        // 3. Construye la consulta base seleccionando campos del IndicadorMunicipal
        //    ¡Asegúrate de seleccionar TODOS los campos base que necesites!
        $query = IndicadorMunicipal::where('id_municipio', $municipioId)->where('publica', '1')
            ->select([
                'id', // ID del IndicadorMunicipal
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
            // Subconsulta para 'dato'
            $query->addSelect([
                "dato_{$year}" => ResultadoIndicadorMunicipal::select('dato')
                    // Correlación: Vincula con el ID del indicador principal
                    ->whereColumn('id_indicador', 'indicadores_municipales.id') // ¡Verifica nombre tabla principal!
                    // Filtro por año
                    ->where('año', $year) // ¡Verifica nombre columna 'anio'!
                    // Orden para obtener el MÁS RECIENTE dentro del año - ¡¡AJUSTA ESTA LÓGICA!!
                    ->orderBy('periodo', 'desc') // EJEMPLO: si 'periodo' indica cuál es más reciente (mayor es más reciente)
                    // ->orderBy('fecha_registro', 'desc') // Alternativa: si usas una fecha
                    // ->orderBy('created_at', 'desc') // Alternativa: si usas timestamp de creación
                    // ->orderBy('id', 'desc') // Alternativa: si el ID más alto es el más reciente
                    ->limit(1) // Toma solo el primero (el más reciente)
            ]);

            // Subconsulta para 'resultado'
            $query->addSelect([
                "resultado_{$year}" => ResultadoIndicadorMunicipal::select('resultado') // ¡Verifica nombre columna 'resultado'!
                    ->whereColumn('id_indicador', 'indicadores_municipales.id')
                    ->where('año', $year)
                    // ¡¡USA EXACTAMENTE EL MISMO orderBy que usaste para 'dato'!!
                    ->orderBy('periodo', 'desc')
                    // ->orderBy('fecha_registro', 'desc')
                    // ->orderBy('created_at', 'desc')
                    // ->orderBy('id', 'desc')
                    ->limit(1)
            ]);
        }
        // ---- NUEVO: Añade subconsultas para ODS ----
        $tablaIndicadores = (new IndicadorMunicipal)->getTable(); // Obtiene nombre de tabla dinámicamente
        $tablaOdsLink = (new IndicadorMunicipalODS)->getTable(); // Nombre tabla relación ODS

        // Subconsulta para ods1 (el primer ODS por ID)
        $query->addSelect([
            'ods1' => IndicadorMunicipalODS::select('id_ods')
                ->whereColumn("{$tablaOdsLink}.id_indicador", "{$tablaIndicadores}.id") // Correlación
                ->orderBy('id_ods', 'asc') // Ordena para obtener consistentemente el 1ro, 2do, 3ro
                // ->orderBy('id', 'asc') // Alternativa: ordenar por PK de la tabla pivote
                ->limit(1)
        ]);

        // Subconsulta para ods2 (el segundo ODS por ID)
        $query->addSelect([
            'ods2' => IndicadorMunicipalODS::select('id_ods')
                ->whereColumn("{$tablaOdsLink}.id_indicador", "{$tablaIndicadores}.id") // Correlación
                ->orderBy('id_ods', 'asc')
                // ->orderBy('id', 'asc')
                ->offset(1) // Salta el primero
                ->limit(1)  // Toma el segundo
        ]);

        // Subconsulta para ods3 (el tercer ODS por ID)
        $query->addSelect([
            'ods3' => IndicadorMunicipalODS::select('id_ods')
                ->whereColumn("{$tablaOdsLink}.id_indicador", "{$tablaIndicadores}.id") // Correlación
                ->orderBy('id_ods', 'asc')
                // ->orderBy('id', 'asc')
                ->offset(2) // Salta los dos primeros
                ->limit(1)  // Toma el tercero
        ]);

        // ---- FIN NUEVO ----

        // 5. Ejecuta la consulta final
        //    Puedes añadir un orderBy para la lista general de indicadores si quieres
        $datos = $query->with([
            'periodicidad', // Asume que la relación se llama así en el modelo
            'tipo',         // Asume que la relación se llama así
            'nivel',        // Asume que la relación se llama así
            'dimension',    // Asume que la relación se llama así
            'municipio'     // Carga el municipio para cada indicador
        ])
            ->orderBy('indicador') // Ordena los indicadores alfabéticamente, por ejemplo
            ->get();

        // dd($datos);
        // --- Fin de la Consulta Mejorada ---

        // 6. Verifica si se encontraron indicadores (la consulta principal)
        if ($datos->isEmpty()) {
            // Decide qué hacer: redirigir, mostrar mensaje, abortar...
            return redirect()->back()->with('warning', 'No hay indicadores definidos para este municipio.');
            // abort(404, 'No hay indicadores definidos para este municipio.');
        }

        // 7. Prepara nombre base para el archivo de descarga
        $fechaActual = Carbon::now()->format('Ymd');
        // Usar Str::slug para un nombre de archivo seguro
        $nombreBaseArchivo = Str::slug($municipio->nombre) . "_datos_{$fechaActual}";

        // 8. Intenta generar y devolver la respuesta según el formato
        try {
            switch ($formato) {
                case 'xlsx':
                    // Llama a la función auxiliar pasando los datos y los años
                    return $this->generarExcel($datos, $nombreBaseArchivo . '.xlsx', $municipio->nombre, $years);
                case 'csv':
                    // Llama a la función auxiliar pasando los datos y los años
                    return $this->generarCsv($datos, $nombreBaseArchivo . '.csv', $municipio->nombre, $years);
                case 'json':
                    // El JSON ya contendrá las columnas dato_YYYY y resultado_YYYY
                    return Response::json($datos, 200, [ // Añade cabeceras directamente
                        'Content-Disposition' => 'attachment; filename="' . $nombreBaseArchivo . '.json"',
                        'Content-Type' => 'application/json',
                    ]);
                default:
                    // Si el formato no es válido (aunque la ruta ya lo podría restringir)
                    abort(400, 'Formato no válido solicitado.');
            }
        } catch (\Exception $e) {
            // Considera loguear el error para depuración
            Log::error("Error generando descarga $formato para municipio $municipioId: " . $e->getMessage(), ['exception' => $e]);

            // Devuelve un error genérico al usuario
            // Puedes personalizar esto, quizás redirigiendo con un mensaje de error
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
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \Exception Si la plantilla no se encuentra o hay error al leerla.
     */
    private function generarExcel($datos, $nombreArchivo, $nombreMunicipio, $years)
    {
        // --- OPCIÓN 1: Usando Plantilla Excel ---
        // --- Asegúrate que la ruta y el nombre sean correctos ---
        $templatePath = storage_path('app/plantillas/plantilla-mun.xlsx'); // <-- ¡AJUSTA RUTA/NOMBRE PLANTILLA!

        if (!file_exists($templatePath)) {
            // Log::error("Plantilla Excel no encontrada: " . $templatePath);
            throw new \Exception("Plantilla Excel no encontrada en {$templatePath}");
        }

        try {
            // Carga la plantilla existente
            $spreadsheet = IOFactory::load($templatePath);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            // Log::error("Error al leer plantilla Excel: " . $e->getMessage());
            throw new \Exception("Error al leer la plantilla Excel: " . $e->getMessage());
        }

        // Selecciona la hoja donde escribirás
        $sheet = $spreadsheet->getActiveSheet();
        // $sheet = $spreadsheet->getSheetByName('NombreDeTuHoja'); // O selecciona por nombre

        // --- Escribe los datos en la plantilla ---
        // Decide cómo mapear: ¿Datos generales en celdas fijas? ¿Una tabla a rellenar?

        // Ejemplo A: Poner datos generales en celdas fijas (si aplica)
        // $sheet->setCellValue('C3', $nombreMunicipio); // Nombre del municipio
        // $sheet->setCellValue('C4', 'Reporte generado el: ' . now()->format('d/m/Y H:i')); // Fecha generación

        // Ejemplo B: Rellenar una tabla en la plantilla (asumiendo que empieza en fila X)
        $filaInicioTabla = 2; // <-- ¡AJUSTA! Fila donde empiezan los datos en tu plantilla
        $filaActual = $filaInicioTabla;
        foreach ($datos as $dato) {
            // Mapea columnas base del $dato a columnas de tu plantilla
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
            $colIndexLetra = 'AB'; // <-- ¡AJUSTA! Letra de columna donde empiezan los datos anuales
            foreach ($years as $year) {
                $datoCol = "dato_{$year}";
                $resultadoCol = "resultado_{$year}";

                // Escribe Dato YYYY
                $sheet->setCellValue($colIndexLetra . $filaActual, $dato->$datoCol);
                // Podrías querer aplicar formato numérico aquí si la plantilla no lo tiene
                // $sheet->getStyle($colIndexLetra . $filaActual)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
                $colIndexLetra++; // Avanza a la siguiente letra de columna (F, H, J, ...)

                // Escribe Resultado YYYY
                $sheet->setCellValue($colIndexLetra . $filaActual, $dato->$resultadoCol);
                $colIndexLetra++; // Avanza a la siguiente letra de columna (G, I, K, ...)
            }
            $filaActual++; // Pasa a la siguiente fila para el próximo indicador
        }

        // --- Fin Escritura en Plantilla ---


        // --- OPCIÓN 2: Generando Excel desde Cero (Descomenta si no usas plantilla) ---
        /*
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Datos ' . Str::limit($nombreMunicipio, 25)); // Título corto para la hoja

        // Encabezados Base
        $col = 'A'; // Empezar en columna A
        $sheet->setCellValue($col++.'1', 'ID');
        $sheet->setCellValue($col++.'1', 'Indicador');
        $sheet->setCellValue($col++.'1', 'Instrumento');
        $sheet->setCellValue($col++.'1', 'Eje Indicador');
        $sheet->setCellValue($col++.'1', 'Unidad Medida');
        // ... Añade el resto de tus encabezados base ...

        // Encabezados Anuales Dinámicos
        foreach ($years as $year) {
            $sheet->setCellValue($col++.'1', "Dato {$year}");
            $sheet->setCellValue($col++.'1', "Resultado {$year}");
        }
        $lastCol = chr(ord($col)-1); // Obtiene la última letra de columna usada para estilos
        $sheet->getStyle('A1:'.$lastCol.'1')->getFont()->setBold(true); // Poner encabezados en negrita

        // Escribir Datos
        $fila = 2; // Empezar datos en fila 2
        foreach ($datos as $dato) {
            $col = 'A'; // Reiniciar columna para cada fila
            // Escribir datos base
            $sheet->setCellValue($col++.$fila, $dato->id);
            $sheet->setCellValue($col++.$fila, $dato->indicador);
            $sheet->setCellValue($col++.$fila, $dato->instrumento);
            $sheet->setCellValue($col++.$fila, $dato->eje_indicador);
            $sheet->setCellValue($col++.$fila, $dato->unidad_medida);
            // ... Escribe el resto de tus datos base ...

            // Escribir datos anuales dinámicos
            foreach ($years as $year) {
                 $datoCol = "dato_{$year}";
                 $resultadoCol = "resultado_{$year}";
                 $sheet->setCellValue($col++.$fila, $dato->$datoCol);         // Dato YYYY
                 $sheet->setCellValue($col++.$fila, $dato->$resultadoCol); // Resultado YYYY
            }
            $fila++; // Siguiente fila
        }

        // Autoajustar ancho de todas las columnas usadas
        foreach (range('A', $lastCol) as $colLetter) {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
        */
        // --- Fin Generación desde Cero ---


        // --- Preparar y Devolver Respuesta Streamed (Común para ambas opciones) ---
        $writer = new Xlsx($spreadsheet);

        // Usar StreamedResponse para eficiencia de memoria
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        // Establecer cabeceras para la descarga
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $nombreArchivo . '"');
        $response->headers->set('Cache-Control', 'max-age=0');
        $response->headers->set('Pragma', 'public'); // Necesario para compatibilidad con IE

        return $response; // Devuelve la respuesta HTTP para descargar el archivo
    }

    /**
     * Función auxiliar para generar la respuesta CSV.
     * Adaptada para coincidir con la estructura de columnas del Excel generado.
     *
     * @param \Illuminate\Support\Collection $datos La colección de datos a exportar (ya debe incluir datos anuales y ODS).
     * @param string $nombreArchivo El nombre deseado para el archivo descargado.
     * @param string $nombreMunicipio El nombre del municipio.
     * @param array $years El array de años (ej. [2019, 2020, ..., 2025]) para las columnas dinámicas.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function generarCsv($datos, $nombreArchivo, $nombreMunicipio, $years)
    {
        // Si no hay datos, podríamos devolver una respuesta vacía o manejarlo antes
        if ($datos->isEmpty()) {
            return Response::make('', 204); // 204 No Content
        }

        // --- 1. Define los encabezados COMPLETOS en el orden del Excel ---
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

        // --- 2. Añade encabezados anuales dinámicamente (igual que antes) ---
        foreach ($years as $year) {
            $cabecerasCsv[] = "Dato {$year}";       // AB, AD, AF, ...
            $cabecerasCsv[] = "Resultado {$year}";  // AC, AE, AG, ...
        }

        // --- 3. Prepara las cabeceras HTTP (igual que antes) ---
        $headersHttp = [
            'Content-Type'              => 'text/csv; charset=utf-8', // UTF-8 importante
            'Content-Disposition'       => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'                    => 'public',
            'Cache-Control'             => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'                   => '0',
        ];

        // --- 4. Crea la respuesta Streamed usando un Callback (igual que antes) ---
        $callback = function () use ($datos, $cabecerasCsv, $nombreMunicipio, $years) { // Añadido $nombreMunicipio a use()
            $file = fopen('php://output', 'w');

            // BOM UTF-8 (Recomendado para Excel)
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Escribe la fila de encabezados completa
            fputcsv($file, $cabecerasCsv);

            // --- 5. Recorre los datos y construye cada fila del CSV ---
            //    (Asumiendo que 'periodicidad', 'tipo', 'nivel', 'dimension'
            //     fueron eager-loaded en la consulta principal)
            foreach ($datos as $dato) {
                // Construye la fila en el MISMO ORDEN que los encabezados
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
                    $dato->meta_2024 ?? '',                    // J - Corregido
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
                    // Formatear fechas si existen, si no, string vacío
                    $dato->created_at ? $dato->created_at->format('Y-m-d H:i:s') : '', // U
                    $dato->updated_at ? $dato->updated_at->format('Y-m-d H:i:s') : '', // V
                    $dato->proxima_actualizacion ?? '',        // W
                    $nombreMunicipio,                          // X - Usa la variable pasada
                    $dato->ods1 ?? '',                         // Y - Usar '' como default es común en CSV
                    $dato->ods2 ?? '',                         // Z
                    $dato->ods3 ?? '',                         // AA
                ];

                // Añade datos anuales dinámicamente (igual que antes)
                foreach ($years as $year) {
                    $datoCol = "dato_{$year}";
                    $resultadoCol = "resultado_{$year}";
                    $filaParaCsv[] = $dato->$datoCol ?? ''; // Añade dato o string vacío
                    $filaParaCsv[] = $dato->$resultadoCol ?? ''; // Añade resultado o string vacío
                }

                // Escribe la fila completa en el archivo CSV
                fputcsv($file, $filaParaCsv);
            }

            // Cierra el flujo de salida
            fclose($file);
        };

        // --- 6. Devuelve la respuesta Streamed (igual que antes) ---
        return Response::stream($callback, 200, $headersHttp);
    }

    // --- IMPORTANTE: Actualiza la llamada en descargarDatosMunicipio ---
    // Dentro del switch ($formato) en descargarDatosMunicipio:
    /* case 'csv':
    // Asegúrate de pasar $municipio->nombre aquí también
    return $this->generarCsv($datos, $nombreBaseArchivo . '.csv', $municipio->nombre, $years);
*/
}
