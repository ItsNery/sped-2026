<?php

namespace App\Http\Controllers;

use App\Models\Indicador;
use App\Models\SliderInicio;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\CarruselIndicador;
use App\Models\CatRegion;
use App\Models\Institucion;
use App\Models\Odses;
use Illuminate\Support\Facades\Log;
use App\Models\CatProgramaDerivadoSectorial;
use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\CatProgramaDerivadoEspecial;
use App\Models\CatProgramaDerivadoRegional;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Indicador $indicador)
    {
        // 1. Cargamos el indicador con sus relaciones.
        // AGREGAMOS 'indicadorable' aquí. Esto hará una sola consulta inteligente 
        // para traer el programa sectorial, institucional o especial correspondiente.
        $indicador->load(['datosAnuales' => function ($q) {
            $q->where('validado', true);
        }, 'ods', 'indicadorable']);

        // --- LÓGICA DE COLORES ---

        $colorFinal = null;
        $colorPorDefectoGeneral = '#691A32';

        // A. INTENTO 1: Obtener color directamente de la relación polimórfica (La forma elegante)
        // Verificamos si existe la relación y si tiene la propiedad color
        if ($indicador->indicadorable && isset($indicador->indicadorable->color)) {
            $colorFinal = $indicador->indicadorable->color;
        }

        // B. INTENTO 2: Si la relación polimórfica falló (está vacía o no tiene color),
        // intentamos buscar por TEMÁTICA (tu lógica de respaldo)
        if (!$colorFinal && $indicador->tematica) {
            // Nota: Asumo que mantienes tu lógica de $coloresBase o la consultas aquí si es necesario.
            // Para mantener este ejemplo limpio, hago una consulta directa rápida, 
            // pero puedes usar tu closure $obtenerColorBase si prefieres.
            $colorFinal = DB::table('cat_colores')
                ->where('tipo', 'programa') // Ajusta el tipo según tu BD
                ->where('clave', $indicador->tematica)
                ->value('color');
        }

        // C. INTENTO 3: Si todo falla, buscar por PROGRAMA GENERAL
        if (!$colorFinal && $indicador->programa) {
            $colorFinal = DB::table('cat_colores')
                ->where('tipo', 'programa')
                ->where('clave', $indicador->programa)
                ->value('color');
        }

        // 4. Asignar el color final o el default
        $indicador->color = $colorFinal ?? $colorPorDefectoGeneral;

        return view('ficha-tecnica', compact('indicador'));
    }

    public function ped($num)
    {
        Log::debug("HomeController@ped: Solicitud para eje/num: {$num}");

        // 1. Realiza la consulta específica para obtener los indicadores
        $indicadoresCollection = $this->consultarIndicadoresPed($num);

        if ($indicadoresCollection->isEmpty()) {
            Log::warning("HomeController@ped: No se encontraron indicadores para el eje/num: {$num} desde consultarIndicadoresPed.");
        } else {
            Log::debug("HomeController@ped: Indicadores encontrados inicialmente: " . $indicadoresCollection->count());
        }

        // 2. Procesar cada indicador para agregar el dato más reciente
        // Esta lógica se mantiene, asumiendo que obtenerDatoReciente está corregido para la nueva estructura de DatoAnual
        $indicadoresCollection->each(function ($indicador) {
            // $indicador->datosAnuales ya está cargado por el 'with' en consultarIndicadoresPed
            $datoRecienteInfo = $this->obtenerDatoReciente($indicador->datos_anuales_validados); // Usar solo validados

            $anioParaVista = $datoRecienteInfo['anio'];
            $valorParaVista = $datoRecienteInfo['valor'];

            // Fallback a linea_base si no se encontró un dato anual reciente con valor
            if (is_null($valorParaVista)) {
                // Log::debug("HomeController@Ped: No se encontró dato reciente para Indicador ID {$indicador->id}. Usando datos de línea base.");
                $anioParaVista = $indicador->linea_base;
                $valorOriginalLB = $indicador->dato_linea_base;
                if ($valorOriginalLB !== null && trim((string)$valorOriginalLB) !== '') {
                    $valorNumericoLB = filter_var($valorOriginalLB, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
                    if (is_numeric($valorNumericoLB)) {
                        $valorParaVista = number_format((float)str_replace(',', '', $valorNumericoLB), 2, '.', '');
                    } else {
                        $valorParaVista = $valorOriginalLB;
                    }
                } else {
                    $valorParaVista = 'Sin datos';
                }
            }
            $indicador->setAttribute('dato_reciente', $valorParaVista);
            $indicador->setAttribute('anio_reciente', $anioParaVista);
        });

        // 3. NUEVO: Agrupar la colección de indicadores por el campo 'tematica'
        // El resultado será una colección donde las claves son los nombres de las temáticas
        // y los valores son colecciones de los indicadores que pertenecen a esa temática.
        $indicadoresAgrupados = $indicadoresCollection->groupBy('tematica');

        // Opcional: Si quieres que las temáticas aparezcan ordenadas alfabéticamente en la vista
        // $indicadoresAgrupados = $indicadoresAgrupados->sortKeys();

        // Log::info("HomeController@Ped: Devolviendo vista 'eje{$num}-ped2' con indicadores agrupados por temática. Número de grupos de temáticas: " . $indicadoresAgrupados->count());

        // 4. Devuelve la vista con los datos agrupados.
        // Es buena idea cambiar el nombre de la variable en compact para reflejar que está agrupada.
        return view('eje' . $num . '-ped', ['indicadoresAgrupados' => $indicadoresAgrupados]);
    }
    // Consulta específica para los indicadores de PED.
    private function consultarIndicadoresPed($num)
    {
        $programas = [
            1 => 'Humanismo con Bienestar',
            2 => 'Prosperidad y Estabilidad Económica',
            3 => 'Estado de Derecho, Seguridad y Justicia',
            4 => 'Desarrollo Urbano y Crecimiento Sostenible',
            5 => 'Gobierno Transformador y de Resultados',
            6 => 'Por Amor a Puebla',
        ];

        if (!array_key_exists($num, $programas)) {
            // Log para saber si el número no es válido
            // Log::warning("HomeController@consultarIndicadoresPed: El número de eje '{$num}' no es una clave válida en el array de programas. Se devolverá una colección vacía.");
            return collect(); // Devuelve una colección vacía si el número no es válido
        }

        $nombreProgramaFiltrar = $programas[$num]; // Nombre del programa a usar en el 'where'
        $nombreProgramaFijo = 'Plan Estatal de Desarrollo 2024-2030'; // Nombre fijo para el campo 'programa'

        // Log::debug("HomeController@consultarIndicadoresPed: Buscando indicadores con programa_derivado='{$nombreProgramaFiltrar}' AND programa='{$nombreProgramaFijo}'.");

        // Construir la consulta
        $query = Indicador::with([
            'datosAnuales' => function ($q_datos) {
                // Seleccionar solo las columnas necesarias de DatoAnual para optimizar
                // Si necesitas más campos de datosAnuales en la vista, añádelos aquí.
                $q_datos->where('validado', true) // Filtro explícito para seguridad en query
                    ->select('id', 'id_indicador', 'anio', 'valor_dato', 'validado' /*, 'resultados', 'observaciones', 'evidencia', 'fecha_actualizacion' */);
            },
            'ods' // Asume que la relación 'ods' carga lo necesario o selecciona columnas específicas si es preciso.
        ])
            ->where('programa', $nombreProgramaFiltrar)
            ->where('programa_derivado', $nombreProgramaFijo)
            ->orderBy('id', 'asc');

        // Opcional: Loguear la consulta SQL para depuración avanzada
        // Log::debug("HomeController@consultarIndicadoresPed: SQL Query: " . $query->toSql(), $query->getBindings());

        $indicadores = $query->get();

        if ($indicadores->isEmpty()) {
            Log::info("HomeController@consultarIndicadoresPed: No se encontraron indicadores para programa_derivado='{$nombreProgramaFiltrar}' y programa='{$nombreProgramaFijo}'.");
        } else {
            Log::info("HomeController@consultarIndicadoresPed: Se encontraron {$indicadores->count()} indicadores para programa_derivado='{$nombreProgramaFiltrar}' y programa='{$nombreProgramaFijo}'.");
        }

        return $indicadores;
    }

    private function obtenerDatoReciente($datosAnualesCollection) // El parámetro es la colección de objetos DatoAnual
    {
        // Paso 1: Verificar si la colección es válida y no está vacía
        if (!$datosAnualesCollection || !($datosAnualesCollection instanceof \Illuminate\Database\Eloquent\Collection) || $datosAnualesCollection->isEmpty()) {
            // Log::debug('HomeController@obtenerDatoReciente: La colección de datos anuales está vacía, no es válida o es null.');
            return [
                'anio' => null,
                'valor' => null, // Se devuelve null; el fallback a linea_base se manejará en el método que llama (modPed)
            ];
        }

        // Paso 2: Encontrar el dato más reciente con valor
        // Filtramos la colección para registros que tengan 'valor_dato' no nulo y no vacío,
        // luego ordenamos por 'anio' de forma descendente y tomamos el primero.
        $datoRecienteEncontrado = $datosAnualesCollection
            ->filter(function ($datoAnual) {
                // Asegurarse de que valor_dato exista, no sea null y no sea una cadena vacía después de trim
                return isset($datoAnual->valor_dato) &&
                    !is_null($datoAnual->valor_dato) &&
                    trim((string) $datoAnual->valor_dato) !== '';
            })
            ->sortByDesc('anio') // Ordenar por año, el más reciente primero
            ->first();           // Tomar el primer elemento (el más reciente con datos)

        // Paso 3: Procesar el dato encontrado
        if ($datoRecienteEncontrado) {
            $anio = $datoRecienteEncontrado->anio;
            $valorOriginal = $datoRecienteEncontrado->valor_dato;

            // Log::debug("HomeController@obtenerDatoReciente: Dato reciente encontrado - Año: {$anio}, Valor Original: '{$valorOriginal}'");

            try {
                // Intentar formatear el valor. Primero, asegurarse de que es numérico.
                // filter_var es más robusto para limpiar strings que podrían tener caracteres no numéricos.
                $valorNumerico = filter_var($valorOriginal, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);

                if (is_numeric($valorNumerico)) {
                    // number_format necesita un float. El separador de miles se elimina con FILTER_SANITIZE_NUMBER_FLOAT si se usa FILTER_FLAG_ALLOW_THOUSAND
                    // pero number_format no lo quiere para el input.
                    // Mejor asegurarse que el string es un número válido para float.
                    $valorFloat = (float) str_replace(',', '', $valorNumerico); // Quitar comas por si acaso

                    $valorFormateado = number_format($valorFloat, 2, '.', ''); // Sin separador de miles para el output
                    // Log::debug("HomeController@obtenerDatoReciente: Valor numérico '{$valorNumerico}', formateado a: '{$valorFormateado}'");
                    return [
                        'anio' => $anio,
                        'valor' => $valorFormateado,
                    ];
                } else {
                    // Si después de sanitizar no es numérico, es un string que no representa un número.
                    // Log::warning("HomeController@obtenerDatoReciente: El valor '{$valorOriginal}' para el año {$anio} no es numérico después de sanitizar. Devolviendo valor original.");
                    return [
                        'anio' => $anio,
                        'valor' => $valorOriginal, // Devolver el valor original si no es numérico
                    ];
                }
            } catch (\Exception $e) {
                // Log::error("HomeController@obtenerDatoReciente: Error al procesar/formatear el valor '{$valorOriginal}' para el año {$anio}: " . $e->getMessage());
                return [ // En caso de cualquier error de formato, devolver el valor original
                    'anio' => $anio,
                    'valor' => $valorOriginal,
                ];
            }
        }

        // Paso 4: Si no se encontró ningún dato anual con valor
        // Log::debug('HomeController@obtenerDatoReciente: No se encontró ningún dato anual con valor válido en la colección.');
        return [
            'anio' => null,
            'valor' => null, // Devolver null para que el método que llama decida el fallback
        ];
    }

    public function mostrarListadoSectoriales()
    {
        $sectoriales = CatProgramaDerivadoSectorial::has('indicadores')->get();
        return view('ped-programas-sectoriales', compact('sectoriales'));
    }

    public function mostrarSectorial($slug)
    {
        // 1. Buscar Programa
        $programa = CatProgramaDerivadoSectorial::all()->first(function ($item) use ($slug) {
            return Str::slug($item->nombre) === $slug;
        });

        if (!$programa) abort(404, 'Programa no encontrado');

        // 2. Datos estéticos
        $color = $programa->color ?? '#691A32';
        $descripcion = $programa->descripcion ?? 'Sin descripción';
        $imagen = $programa->imagen ?? 'img/secretarias/Sectorial1.jpg';
        $programaData = $programa;

        // 3. Obtener Indicadores (¡Sin bucles extra!)
        $indicadores = Indicador::with(['datosAnuales' => function ($q) {
            $q->where('validado', true);
        }, 'ods'])
            ->where('programa', $programa->nombre)
            ->orderBy('id', 'asc')
            ->get();

        // 4. Retornar Vista
        return view('programa-sectorial', compact('indicadores', 'programa', 'color', 'descripcion', 'imagen', 'programaData'));
    }

    public function mostrarEspecial($slug)
    {
        // 1. Buscar Programa
        $programa = CatProgramaDerivadoEspecial::all()->first(function ($item) use ($slug) {
            return Str::slug($item->nombre) === $slug;
        });

        if (!$programa) abort(404, 'Programa no encontrado');

        // 2. Datos estéticos
        $color = $programa->color ?? '#691A32';
        $descripcion = $programa->descripcion ?? 'Sin descripción';
        $imagen = $programa->imagen ?? 'img/secretarias/Sectorial1.jpg';
        $programaData = $programa;

        // 3. Obtener Indicadores (¡Sin bucles extra!)
        $indicadores = Indicador::with(['datosAnuales' => function ($q) {
            $q->where('validado', true);
        }, 'ods'])
            ->where('programa', $programa->nombre)
            ->orderBy('id', 'asc')
            ->get();

        // 4. Retornar Vista
        return view('programa-especial', compact('indicadores', 'programa', 'color', 'descripcion', 'imagen', 'programaData'));
    }

    public function mostrarListadoEspeciales()
    {
        $especiales = CatProgramaDerivadoEspecial::has('indicadores')->get();
        return view('ped-programas-especiales', compact('especiales'));
    }

    public function mostrarListadoInstitucionales()
    {
        $programas = CatProgramaDerivadoInstitucional::has('indicadores')->get();
        return view('ped-programas-institucionales', compact('programas'));
    }

    public function mostrarInstitucional($slug)
    {
        // 1. Buscar Programa
        $programa = CatProgramaDerivadoInstitucional::all()->first(function ($item) use ($slug) {
            return Str::slug($item->nombre) === $slug;
        });

        if (!$programa) abort(404, 'Programa no encontrado');

        // 2. Datos estéticos
        $color = $programa->color ?? '#691A32';
        $descripcion = $programa->descripcion ?? 'Sin descripción';
        $imagen = $programa->imagen ?? 'img/secretarias/Sectorial1.jpg';
        $programaData = $programa;

        // 3. Obtener Indicadores (¡Sin bucles extra!)
        $indicadores = Indicador::with(['datosAnuales' => function ($q) {
            $q->where('validado', true);
        }, 'ods'])
            ->where('programa', $programa->nombre)
            ->orderBy('id', 'asc')
            ->get();

        // 4. Retornar Vista
        return view('programa-institucional', compact('indicadores', 'programa', 'color', 'descripcion', 'imagen', 'programaData'));
    }

    public function mostrarCarrusel()
    {
        // Log::info('HomeController@mostrarCarrusel: Iniciando carga de datos para el carrusel.');

        // Obtener los datos necesarios
        // MODIFICADO: Eager load de 'indicador' y también 'indicador.datosAnuales' (solo validados)
        $carruselItems = CarruselIndicador::with(['indicador.datosAnuales' => function ($q) {
            $q->where('validado', true);
        }])->get();
        // Log::debug("HomeController@mostrarCarrusel: Se obtuvieron " . $carruselItems->count() . " elementos para el carrusel.");

        // Si $indicadores se usa para algo más que la lógica del carrusel (ej. un dropdown general),
        // esta consulta está bien. Si solo es para datos del carrusel, podría ser redundante.
        $todosLosIndicadores = Indicador::all(); // Esta variable se pasa a la vista como 'indicadores'
        // Log::debug("HomeController@mostrarCarrusel: Se obtuvieron " . $todosLosIndicadores->count() . " indicadores totales (para 'indicadores').");


        $imagenesPath = public_path('img/iconos_indicadores');
        $imagenes = [];
        if (is_dir($imagenesPath)) {
            $imagenes = array_diff(scandir($imagenesPath), ['.', '..']);
            // Log::debug("HomeController@mostrarCarrusel: Se encontraron " . count($imagenes) . " imágenes de iconos.");
        } else {
            // Log::warning("HomeController@mostrarCarrusel: El directorio de imágenes de iconos no existe o no es accesible: " . $imagenesPath);
        }

        // Procesar cada elemento del carrusel
        $carruselItems->each(function ($item) {
            if ($item->indicador) { // $item->indicador ya tiene 'datosAnuales' cargado
                $datoReciente = $this->obtenerDatoRecienteCarrusel($item->indicador); // Pasas la instancia completa del Indicador

                $item->ultimo_dato = $datoReciente['valor'];
                $item->anio_mas_reciente = $datoReciente['anio'];
                // Log::debug("HomeController@mostrarCarrusel: Procesado CarruselItem ID {$item->id}, Indicador ID {$item->indicador->id}. Dato Reciente: Año '{$item->anio_mas_reciente}', Valor '{$item->ultimo_dato}'.");
            } else {
                $item->ultimo_dato = 'Sin datos';
                $item->anio_mas_reciente = null;
                // Log::warning("HomeController@mostrarCarrusel: CarruselItem ID {$item->id} no tiene un indicador asociado.");
            }
        });

        // Esta parte ya estaba bien para la nueva estructura de datosAnuales
        $indicadoresRecientes = Indicador::with('datosAnuales')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
        // Log::debug("HomeController@mostrarCarrusel: Se obtuvieron " . $indicadoresRecientes->count() . " indicadores recientes.");
        // Si la vista 'inicio' necesita mostrar 'dato_reciente' y 'anio_reciente' para estos $indicadoresRecientes,
        // también necesitarían pasar por una lógica similar a la del carrusel, o usar el método
        // $indicador->getValorDatoAnual($anio) que definimos para otra vista, si aplica.

        $sliders = SliderInicio::where('activo', '1')->get();
        // Log::debug("HomeController@mostrarCarrusel: Se obtuvieron " . $sliders->count() . " sliders activos.");

        // Log::info('HomeController@mostrarCarrusel: Preparando para renderizar vista "inicio".');
        return view('inicio', compact(
            'carruselItems', // Renombré $carrusel a $carruselItems para claridad
            'todosLosIndicadores', // Renombré $indicadores a $todosLosIndicadores para claridad
            'imagenes',
            'indicadoresRecientes',
            'sliders'
        ));
    }
    private function obtenerDatoRecienteCarrusel(Indicador $indicador) // El parámetro es el modelo Indicador
    {
        // El Indicador debería tener su relación 'datosAnuales' ya cargada.
        // Usamos el nuevo accessor para obtener solo los validados
        $datosAnualesCollection = $indicador->datos_anuales_validados;

        if ($datosAnualesCollection && $datosAnualesCollection->isNotEmpty()) {
            // Filtrar para obtener el dato más reciente con valor no nulo y no vacío
            $datoRecienteEncontrado = $datosAnualesCollection
                ->filter(function ($datoAnual) {
                    return isset($datoAnual->valor_dato) &&
                        !is_null($datoAnual->valor_dato) &&
                        trim((string) $datoAnual->valor_dato) !== '';
                })
                ->sortByDesc('anio') // Ordenar por año, el más reciente primero
                ->first();           // Tomar el primer elemento

            if ($datoRecienteEncontrado) {
                $anio = $datoRecienteEncontrado->anio;
                $valorOriginal = $datoRecienteEncontrado->valor_dato;
                // Log::debug("HomeController@obtenerDatoRecienteCarrusel: Dato anual reciente para Indicador ID {$indicador->id} - Año: {$anio}, Valor Original: '{$valorOriginal}'");

                try {
                    $valorNumerico = filter_var($valorOriginal, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
                    if (is_numeric($valorNumerico)) {
                        $valorFloat = (float) str_replace(',', '', $valorNumerico);
                        return [
                            'anio' => $anio,
                            'valor' => number_format($valorFloat, 2, '.', ''),
                        ];
                    } else {
                        // Log::warning("HomeController@obtenerDatoRecienteCarrusel: Valor '{$valorOriginal}' (Año {$anio}) no es numérico para Indicador ID {$indicador->id}. Devolviendo original.");
                        return ['anio' => $anio, 'valor' => $valorOriginal];
                    }
                } catch (\Exception $e) {
                    // Log::error("HomeController@obtenerDatoRecienteCarrusel: Error formateando valor '{$valorOriginal}' (Año {$anio}) para Indicador ID {$indicador->id}: " . $e->getMessage());
                    return ['anio' => $anio, 'valor' => $valorOriginal];
                }
            }
        }

        // Fallback a linea_base si no se encontró dato anual reciente con valor
        // Log::debug("HomeController@obtenerDatoRecienteCarrusel: No se encontró dato anual reciente para Indicador ID {$indicador->id}. Usando línea base.");
        $valorLineaBase = $indicador->dato_linea_base;
        // Asumimos que $indicador->linea_base contiene el AÑO de la línea base
        $anioLineaBase = $indicador->linea_base ?? 'Línea base';

        if ($valorLineaBase !== null && trim((string)$valorLineaBase) !== '') {
            try {
                $valorNumericoLB = filter_var($valorLineaBase, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
                if (is_numeric($valorNumericoLB)) {
                    $valorFloatLB = (float) str_replace(',', '', $valorNumericoLB);
                    return [
                        'anio' => $anioLineaBase,
                        'valor' => number_format($valorFloatLB, 2, '.', ''),
                    ];
                } else {
                    // Log::warning("HomeController@obtenerDatoRecienteCarrusel: dato_linea_base '{$valorLineaBase}' no es numérico para Indicador ID {$indicador->id}. Devolviendo original.");
                    return ['anio' => $anioLineaBase, 'valor' => $valorLineaBase];
                }
            } catch (\Exception $e) {
                // Log::error("HomeController@obtenerDatoRecienteCarrusel: Error formateando dato_linea_base '{$valorLineaBase}' para Indicador ID {$indicador->id}: " . $e->getMessage());
                return ['anio' => $anioLineaBase, 'valor' => $valorLineaBase];
            }
        }

        // Log::debug("HomeController@obtenerDatoRecienteCarrusel: No hay dato anual reciente ni dato de línea base válido para Indicador ID {$indicador->id}.");
        return [
            'anio' => $anioLineaBase, // Puede ser el año de la línea base o 'Línea base'
            'valor' => 'Sin datos',
        ];
    }
    public function indicadoresAgenda1()
    {
        $odsResultados = [];

        // Iterar sobre los ODS (del 1 al 17)
        for ($ods = 1; $ods <= 17; $ods++) {
            // Obtener los indicadores para el ODS actual
            $resultados = DB::table('indicadors as i')
                ->join('indicador_ods as io', 'i.id', '=', 'io.id_indicador')
                ->select(DB::raw('COUNT(DISTINCT i.id) AS numero_indicadores, i.programa_derivado'))
                ->where('io.id_ods', $ods)
                ->where('i.version_2024', 0)
                ->groupBy('i.programa_derivado')
                ->get();

            // Guardar los resultados en el array usando el ID del ODS como clave
            $odsResultados[$ods] = $resultados;
        }
        $totalIndicadores = Indicador::where('version_2024', '0')->count();
        // Pasar los resultados agrupados a la vista
        return view('agenda', compact('odsResultados', 'totalIndicadores'));
    }
    public function indicadoresAgenda2()
    {
        $odsResultados = [];

        // Iterar sobre los ODS (del 1 al 17)
        for ($ods = 1; $ods <= 17; $ods++) {
            // Obtener los indicadores para el ODS actual
            $resultados = DB::table('indicadors as i')
                ->join('indicador_ods as io', 'i.id', '=', 'io.id_indicador')
                ->select(DB::raw('COUNT(DISTINCT i.id) AS numero_indicadores, i.programa_derivado'))
                ->where('io.id_ods', $ods)
                ->where('i.version_2024', 1)
                ->groupBy('i.programa_derivado')
                ->get();

            // Guardar los resultados en el array usando el ID del ODS como clave
            $odsResultados[$ods] = $resultados;
        }
        $totalIndicadores = Indicador::where('version_2024', '1')->count();

        // Pasar los resultados agrupados a la vista
        return view('agenda2', compact('odsResultados', 'totalIndicadores'));
    }
    public function mostrarListadoRegionales()
    {
        $regionales = CatProgramaDerivadoRegional::has('indicadores')->get();
        return view('ped-programas-regionales', compact('regionales'));
    }

    public function mostrarRegional($slug)
    {
        // 1. Buscar Programa
        $programa = CatProgramaDerivadoRegional::all()->first(function ($item) use ($slug) {
            return Str::slug($item->nombre) === $slug;
        });

        if (!$programa) abort(404, 'Programa no encontrado');

        // 2. Datos estéticos
        $color = $programa->color ?? '#691A32';
        $descripcion = $programa->descripcion ?? 'Sin descripción';
        $imagen = $programa->imagen ?? 'img/secretarias/Sectorial1.jpg';
        $programaData = $programa;

        // 3. Obtener Indicadores (¡Sin bucles extra!)
        $indicadores = Indicador::with(['datosAnuales' => function ($q) {
            $q->where('validado', true);
        }, 'ods'])
            ->where('programa', $programa->nombre)
            ->orderBy('id', 'asc')
            ->get();

        // 4. Retornar Vista
        return view('programa-regional', compact('indicadores', 'programa', 'color', 'descripcion', 'imagen', 'programaData'));
    }

    public function generarFicha($id)
    {
        $indicador = Indicador::with(['datosAnuales' => function ($q) {
            $q->where('validado', true);
        }, 'ods'])
            ->where('id', $id)
            ->first();

        if (!$indicador) {
            return redirect()->back()->with('error', 'Indicador no encontrado.');
        }

        // --- REAPLICAR LÓGICA PARA OBTENER EL COLOR ---
        $coloresBase = DB::table('cat_colores')
            ->whereIn('tipo', ['programa'])
            ->get()
            ->groupBy('tipo');

        $obtenerColorBase = function ($tipo, $clave, $default = null) use ($coloresBase) {
            if (!isset($coloresBase[$tipo])) return $default;
            $colorEncontrado = $coloresBase[$tipo]->firstWhere('clave', $clave);
            return $colorEncontrado ? $colorEncontrado->color : $default;
        };

        $colorFinal = null;
        $colorPorDefectoGeneral = '#691A32';

        switch ($indicador->programa_derivado) {
            case 'Programa Especial':
                $colorFinal = DB::table('cat_programas_derivados_especiales')
                    ->where('nombre', $indicador->programa)
                    ->value('color');
                break;
            case 'Programa Institucional':
                $colorFinal = DB::table('cat_programas_derivados_institucionales')
                    ->where('nombre', $indicador->programa)
                    ->value('color');
                break;
            case 'Programa Sectorial':
                $colorFinal = DB::table('cat_programas_derivados_sectoriales')
                    ->where('nombre', $indicador->programa)
                    ->value('color');
                break;
        }

        if (!$colorFinal) {
            $colorFinal = $obtenerColorBase('tematica_v1', $indicador->tematica);
        }
        if (!$colorFinal) {
            $colorFinal = $obtenerColorBase('programa', $indicador->programa);
        }
        $indicador->color = $colorFinal ?? $colorPorDefectoGeneral;
        // --- FIN DE LA LÓGICA DEL COLOR ---
        // Obtener el dato más reciente (solo validados)
        $datoReciente = $this->obtenerDatoReciente($indicador->datos_anuales_validados);
        $indicador->setAttribute('dato_reciente', $datoReciente['valor']);
        $indicador->setAttribute('anio_reciente', $datoReciente['anio']);

        // Pasar los datos a la vista
        return view('generar-ficha', compact('indicador'));
    }
    public function capacitacion2025()
    {
        return view('capacitacion-2025');
    }

    /**
     * Muestra la vista interactiva de documentación y consulta de la API de indicadores.
     */
    public function apiDocs()
    {
        $instituciones = Institucion::select('id', 'nombre')->whereNotIn('nombre', ['Administración del SPED', 'Dependencia'])->orderBy('nombre', 'asc')->get();
        $ods = Odses::select('id', 'nombre')->orderBy('id', 'asc')->get();
        $programasDerivados = Indicador::distinct()
            ->whereNotNull('programa_derivado')
            ->where('programa_derivado', '!=', '')
            ->orderBy('programa_derivado', 'asc')
            ->pluck('programa_derivado');

        return view('publico.api_docs', compact('instituciones', 'ods', 'programasDerivados'));
    }

    /**
     * Muestra la vista de detalle de un indicador para la consola de API.
     *
     * @param  string  $id_or_slug
     * @return \Illuminate\Http\Response
     */
    public function apiIndicatorDetail($id_or_slug)
    {
        return view('publico.api_indicator_detail', compact('id_or_slug'));
    }
}

