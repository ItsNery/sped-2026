<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\CarruselIndicador;
use App\Models\Indicador;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CarruselIndicadorController extends Controller
{
    /**
     * Define el middleware de permisos para las acciones del controlador.
     * Utiliza los permisos de Spatie para un control de acceso granular.
     */
    public function __construct()
    {
        $this->middleware('permission:ver-ind-carrusel|crear-ind-carrusel|editar-ind-carrusel|borrar-ind-carrusel', ['only' => ['index']]);
        $this->middleware('permission:crear-ind-carrusel', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-ind-carrusel', ['only' => ['edit', 'update']]);
        $this->middleware('permission:borrar-ind-carrusel', ['only' => ['destroy']]);
    }

    /**
     * Muestra la lista de ítems del carrusel y prepara los datos para el formulario modal.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        Log::info('CarruselIndicadorController@index: Iniciando.');

        // 1. Obtener los ítems del carrusel, cargando la relación 'indicador' y 'datosAnuales'
        $carruselItems = CarruselIndicador::with('indicador.datosAnuales')
            ->orderBy('id', 'asc')
            ->get();
        Log::debug("CarruselIndicadorController@index: Se obtuvieron " . $carruselItems->count() . " ítems del carrusel.");

        // 2. Obtener los IDs de los indicadores que YA ESTÁN EN EL CARRUSEL
        $idsIndicadoresEnCarrusel = $carruselItems->pluck('indicador.id')->filter()->unique()->toArray();
        // ->filter() sin argumentos elimina valores null (por si algún CarruselItem no tiene indicador)
        // ->unique() por si un indicador estuviera (incorrectamente) más de una vez
        Log::debug("CarruselIndicadorController@index: IDs de indicadores ya en el carrusel: ", $idsIndicadoresEnCarrusel);

        // 3. Lista de indicadores para el selector (EXCLUYENDO los ya usados)
        $indicadoresParaSelector = Indicador::whereNotIn('id', $idsIndicadoresEnCarrusel) 
            ->orderBy('nombre', 'asc')
            ->get(['id', 'nombre']); // Solo obtener id y nombre para el selector
        Log::debug("CarruselIndicadorController@index: Se obtuvieron " . $indicadoresParaSelector->count() . " indicadores disponibles para el selector.");

        // 4. (Opcional si lo usa la vista) Imágenes de iconos
        $imagenesIconosPath = public_path('img/iconos_indicadores');
        $imagenesIconos = [];
        if (is_dir($imagenesIconosPath)) {
            $imagenesIconos = array_diff(scandir($imagenesIconosPath), ['.', '..']);
        } else {
            Log::warning("CarruselIndicadorController@index: Directorio de iconos no encontrado: " . $imagenesIconosPath);
        }
        Log::debug("CarruselIndicadorController@index: Se encontraron " . count($imagenesIconos) . " imágenes de iconos.");

        // 5. Procesar cada elemento del carrusel para añadir/formatear datos para la vista
        // (Esta lógica se mantiene igual que antes para los ítems que SÍ están en el carrusel)
        $carruselItems->each(function ($item) {
            if ($item->indicador) {
                $valorReciente = $item->indicador->ultimo_dato;
                $anioReciente = $item->indicador->anio_ultimo_dato;

                if ($valorReciente !== null) {
                    $valorLimpio = str_replace(',', '', (string)$valorReciente);
                    if (is_numeric($valorLimpio)) {
                        $item->ultimo_dato_formateado = number_format((float)$valorLimpio, 2, '.', ',');
                    } else {
                        $item->ultimo_dato_formateado = $valorReciente;
                    }
                } else {
                    $item->ultimo_dato_formateado = 'Sin datos';
                }
                $item->anio_mas_reciente = $anioReciente ?? 'N/A';
                Log::debug("CarruselIndicadorController@index: Procesado CarruselItem ID {$item->id}, Indicador ID {$item->indicador->id}. Dato: Año '{$item->anio_mas_reciente}', Valor Formateado '{$item->ultimo_dato_formateado}'.");
            } else {
                $item->ultimo_dato_formateado = 'Indicador no asignado';
                $item->anio_mas_reciente = null;
                Log::warning("CarruselIndicadorController@index: CarruselItem ID {$item->id} no tiene un indicador asociado.");
            }
        });

        Log::info('CarruselIndicadorController@index: Preparando para renderizar vista "panel-carrusel-indicadores.index".');
        $indicadores = $indicadoresParaSelector;
        return view('panel-carrusel-indicadores.index', [
            'carrusel' => $carruselItems,
            'indicadores' => $indicadores,
            'imagenes' => $imagenesIconos
        ]);
    }


    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        //
    }

    /**
     * Almacena un nuevo ítem en el carrusel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_indicador' => 'required|exists:indicadors,id',
            'imagen' => 'required|string',
        ]);

        CarruselIndicador::updateOrCreate(['id' => $request->id], $data);

        return redirect()->route('panel-carrusel-indicadores.index')->with('success', 'El indicador se ha agregado al carrusel correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CarruselIndicador  $carruselIndicador

     */
    public function show(CarruselIndicador $carruselIndicador)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CarruselIndicador  $carruselIndicador

     */
    public function edit(CarruselIndicador $carruselIndicador)
    {
        //
    }

/**
     * Actualiza un ítem existente en el carrusel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request,  $id)
    {
        // Validar los datos enviados
        $data = $request->validate([
            'id_indicador' => 'required|exists:indicadors,id', // El indicador debe existir en la tabla de indicadores
            'imagen' => 'required|string', // La imagen debe ser una cadena válida
        ]);

        // Encontrar el registro en el carrusel y actualizarlo
        $carrusel = CarruselIndicador::findOrFail($id);
        $carrusel->update($data);

        return redirect()->route('panel-carrusel-indicadores.index')->with('success', 'Los cambios se han guardado correctamente');
    }

   /**
     * Elimina un ítem del carrusel.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        CarruselIndicador::findOrFail($id)->delete();
        return redirect()->route('panel-carrusel-indicadores.index')->with('success', 'El indicador se ha eliminado del carrusel correctamente');
    }
    private function obtenerDatoReciente($indicador)
    {
        // Consulta para obtener los datos históricos del indicador
        $datosAnuales = DB::table('datos_anuales_indicadores')
            ->where('id_indicador', $indicador->id)
            ->select([
                'dato_2024',
                'dato_2023',
                'dato_2022',
                'dato_2021',
                'dato_2020',
                'dato_2019',
                'dato_2018',
                'dato_2017',
                'dato_2016',
                'dato_2015',
                'dato_2014',
                'dato_2013',
                'dato_2012',
                'dato_2011',
                'dato_2010',
            ])
            ->first();

        if ($datosAnuales) {
            $anioActual = date('Y');
            // Rango de años para verificar, de más reciente a más antiguo
            $años = range($anioActual, 2010);

            foreach ($años as $año) {
                $campo = "dato_$año";

                if (!is_null($datosAnuales->$campo) && $datosAnuales->$campo !== '') {
                    return [
                        'anio' => $año,
                        'valor' => number_format($datosAnuales->$campo, 2),
                    ];
                }
            }
        }

        // Si no hay datos en los años históricos, usar dato_linea_base
        return [
            'anio' => 'Línea base',
            'valor' => $indicador->dato_linea_base
                ? number_format($indicador->dato_linea_base, 2)
                : 'Sin datos',
        ];
    }
}
