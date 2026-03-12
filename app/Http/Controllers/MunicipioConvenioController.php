<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatMunicipio;
use App\Models\MunicipioConvenio;
use App\Models\IndicadorMunicipal;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MunicipioConvenioController extends Controller
{
    /**
     * Aplica el middleware de permisos a las acciones del controlador.
     */
    public function __construct()
    {
        $this->middleware('permission:ver-municipios-convenio|crear-municipios-convenio|editar-municipios-convenio|borrar-municipios-convenio', ['only' => ['index']]);
        $this->middleware('permission:crear-municipios-convenio', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-municipios-convenio', ['only' => ['edit', 'update']]);
        $this->middleware('permission:borrar-municipios-convenio', ['only' => ['destroy']]);
    }

    /**
     * Muestra una lista de los municipios con convenio.
     * Carga de forma anticipada (eager loading) el municipio y sus indicadores públicos.
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // $municipios = CatMunicipio::all();
        $municipios = CatMunicipio::with('indicadores')->get();
        // $municipiosConConvenio = MunicipioConvenio::all();
        $municipiosConConvenio = MunicipioConvenio::with([
            'municipio.indicadores' => function ($query) {
                $query->where('publica', 1);
            }
        ])->get();
        return view('panel-municipios-convenio.index', compact('municipios', 'municipiosConConvenio'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Almacena un nuevo registro de Municipio con Convenio.
     * Gestiona la subida de 3 archivos: convenio (PDF), ícono (imagen) y banner (imagen).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validar los datos recibidos
        $request->validate([
            'id_municipio' => 'required|exists:cat_municipios,id',
            'objetivo' => 'required|string',
            'convenio' => 'required|file|mimes:pdf',
            'icono' => 'required|file|mimes:jpeg,png,jpg,gif',
            'banner' => 'required|file|mimes:jpeg,png,jpg,gif',
        ], [
            'id_municipio.required' => 'Seleccione un municipio',
            'objetivo.required' => 'Ingrese un objetivo',
            'convenio.required' => 'Seleccione un archivo PDF',
            'icono.required' => 'Seleccione un archivo de imagen',
            'banner.required' => 'Seleccione un archivo de imagen',
        ]);

        // Crear una nueva instancia del modelo
        $municipioConConvenio = new MunicipioConvenio();
        $municipioConConvenio->id_municipio = $request->id_municipio;
        $municipioConConvenio->objetivo = $request->objetivo;

        // Procesar y guardar el archivo del convenio si se cargó
        if ($request->hasFile('convenio')) {
            $rutaGuardarConvenio = 'docs/convenios/';
            $convenio = $request->file('convenio');
            $nombreArchivo = "Convenio_Municipio_{$municipioConConvenio->id_municipio}_" . date('YmdHis') . '.' . $convenio->getClientOriginalExtension();

            // Mover el archivo al directorio especificado
            $convenio->move(public_path($rutaGuardarConvenio), $nombreArchivo);

            // Guardar el nombre del archivo en la base de datos
            $municipioConConvenio->convenio = $rutaGuardarConvenio . $nombreArchivo;
        }

        // Procesar y guardar el archivo del ícono si se cargó
        if ($request->hasFile('icono')) {
            $rutaGuardarIcono = 'img/iconos-municipios/';
            $icono = $request->file('icono');
            $nombreArchivoIcono = "Icono_Municipio_{$municipioConConvenio->id_municipio}_" . date('YmdHis') . '.' . $icono->getClientOriginalExtension();

            // Mover el archivo al directorio especificado
            $icono->move(public_path($rutaGuardarIcono), $nombreArchivoIcono);

            // Guardar el nombre del archivo en la base de datos
            $municipioConConvenio->icono = $rutaGuardarIcono . $nombreArchivoIcono;
        }

        // Procesar y guardar el archivo del banner si se cargó
        if ($request->hasFile('banner')) {
            $rutaGuardarBanner = 'img/banner-municipios/';
            $icono = $request->file('banner');
            $nombreArchivoBanner = "Banner_Municipio_{$municipioConConvenio->id_municipio}_" . date('YmdHis') . '.' . $icono->getClientOriginalExtension();

            // Mover el archivo al directorio especificado
            $icono->move(public_path($rutaGuardarBanner), $nombreArchivoBanner);

            // Guardar el nombre del archivo en la base de datos
            $municipioConConvenio->banner = $rutaGuardarBanner . $nombreArchivoBanner;
        }

        // Guardar el registro en la base de datos
        $municipioConConvenio->save();

        // Redirigir al usuario con un mensaje de éxito
        return redirect()->route('panel-municipios-convenio.index')->with('success', 'Municipio guardado correctamente.');
    }

    /**
     * Muestra una página de detalle pública para un municipio con convenio,
     * incluyendo sus indicadores públicos y sus datos más recientes.
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show(MunicipioConvenio $municipioConvenio)
    {
        $indicadores = IndicadorMunicipal::where('id_municipio', $municipioConvenio->id_municipio)
            ->where('publica', 1)
            ->with('resultados', 'ods')
            ->get();

        // Calcular los valores adicionales para cada indicador
        $indicadores->each(function ($indicador) {
            // Obtener los resultados del indicador
            $resultados = $indicador->resultados;
            if ($resultados->isNotEmpty()) {
                // Encontrar el año más reciente
                $anioMasReciente = $resultados->sortByDesc('año')->first();
                $indicador->aniomasreciente = $anioMasReciente->año;

                // Encontrar el dato más grande para el año más reciente
                $resultadosRecientes = $resultados->where('año', $anioMasReciente->año);
                $periodoMasGrande = $resultadosRecientes->sortByDesc('periodo')->first();

                $indicador->datoaniomasreciente = $periodoMasGrande->dato;
            }
        });
        $totalIndicadores = $indicadores->count();

        return view('detalle-mun', [
            'municipio' => $municipioConvenio, // Renombramos para no romper tu vista
            'indicadores' => $indicadores,
            'totalIndicadores' => $totalIndicadores
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id

     */
    public function edit($id)
    {
        //
    }

    /**
     * Actualiza un registro de Municipio con Convenio.
     * Gestiona el reemplazo opcional de los 3 archivos asociados.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validar los datos del formulario
        $validated = $request->validate([
            'id_municipio' => 'required|exists:cat_municipios,id',
            'objetivo' => 'required|string',
            'convenio' => 'nullable|file|mimes:pdf|max:15048',
            'icono' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10048',
        ], [
            'id_municipio.required' => 'Elija un municipio',
            'id_municipio.exists' => 'El municipio no existe',
            'objetivo.required' => 'Ingrese un objetivo',
            'convenio.max' => 'El archivo de convenio no puede pesar más de 10MB',
            'icono.max' => 'El archivo de ícono no puede pesar más de 10MB',
            'banner.max' => 'El archivo de banner no puede pesar más de 10MB',
        ]);

        // Buscar el registro por ID
        $municipioConvenio = MunicipioConvenio::findOrFail($id);

        // Actualizar campos simples
        $municipioConvenio->id_municipio = $validated['id_municipio'];
        $municipioConvenio->objetivo = $validated['objetivo'];

        // Manejar el archivo del convenio (PDF)
        if ($request->hasFile('convenio')) {
            // Eliminar el archivo anterior si existe
            if ($municipioConvenio->convenio && file_exists(public_path($municipioConvenio->convenio))) {
                unlink(public_path($municipioConvenio->convenio));
            }
            // Guardar el nuevo archivo
            $convenioPath = $request->file('convenio')->move(public_path('docs/convenios/'), uniqid() . '.' . $request->file('convenio')->getClientOriginalExtension());
            $municipioConvenio->convenio = str_replace(public_path(), '', $convenioPath);
        }

        // Manejar el archivo del ícono (imagen)
        if ($request->hasFile('icono')) {
            // Eliminar el archivo anterior si existe
            if ($municipioConvenio->icono && file_exists(public_path($municipioConvenio->icono))) {
                unlink(public_path($municipioConvenio->icono));
            }
            // Guardar el nuevo archivo
            $iconoPath = $request->file('icono')->move(public_path('img/iconos-municipios/'), uniqid() . '.' . $request->file('icono')->getClientOriginalExtension());
            $municipioConvenio->icono = str_replace(public_path(), '', $iconoPath);
        }

        if ($request->hasFile('banner')) {
            // Eliminar el archivo anterior si existe
            if ($municipioConvenio->banner && file_exists(public_path($municipioConvenio->banner))) {
                unlink(public_path($municipioConvenio->banner));
            }
            // Guardar el nuevo archivo
            $bannerPath = $request->file('banner')->move(public_path('img/banner-municipios/'), uniqid() . '.' . $request->file('banner')->getClientOriginalExtension());
            $municipioConvenio->banner = str_replace(public_path(), '', $bannerPath);
        }

        // Guardar los cambios en la base de datos
        $municipioConvenio->save();

        // Redireccionar con un mensaje de éxito
        return redirect()->route('panel-municipios-convenio.index')
            ->with('success', 'Municipio actualizado exitosamente.');
    }

    /**
     * Elimina un registro de Municipio con Convenio y todos sus archivos asociados.
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        $municipio = MunicipioConvenio::findOrFail($id);

        // Eliminar la imagen asociada si existe
        if ($municipio->icono && file_exists(public_path($municipio->icono))) {
            unlink(public_path($municipio->icono));
        }
        if ($municipio->banner && file_exists(public_path($municipio->banner))) {
            unlink(public_path($municipio->banner));
        }
        if ($municipio->convenio && file_exists(public_path($municipio->convenio))) {
            unlink(public_path($municipio->convenio));
        }
        // Eliminar el registro de la base de datos
        $municipio->delete();

        // Si se presentan problemas, puedes usar el siguiente código para eliminar el registro
        // MunicipioConvenio::findOrFail($id)->delete();

        return redirect()->route('panel-municipios-convenio.index')->with('success', 'Municipio eliminado.');
    }

    /**
     * Muestra una página pública con todos los municipios que tienen convenio.
     * @return \Illuminate\View\View
     */
    public function mostrarMunicipiosConvenio()
    {
        $municipiosConvenio = MunicipioConvenio::all();
        return view('planes-mun', compact('municipiosConvenio'));
    }

    /**
     * Muestra una ficha técnica de solo lectura para un indicador municipal.
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showMunicipal($id)
    {
        // Buscamos el indicador municipal con sus relaciones importantes
        $indicador = IndicadorMunicipal::with(['municipio', 'resultados', 'tipo', 'dimension'])
            ->findOrFail($id);

        return view('panel-municipios-convenio.show_municipal', compact('indicador'));
    }
}
