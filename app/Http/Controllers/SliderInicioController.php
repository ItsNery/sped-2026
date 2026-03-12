<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SliderInicio;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SliderInicioController extends Controller
{
    /**
     * Define el middleware de permisos para las acciones del controlador.
     */
    public function __construct()
    {
        $this->middleware('permission:ver-slider-inicio|crear-slider-inicio|editar-slider-inicio|borrar-slider-inicio', ['only' => ['index']]);
        $this->middleware('permission:crear-slider-inicio', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-slider-inicio', ['only' => ['edit', 'update']]);
        $this->middleware('permission:borrar-slider-inicio', ['only' => ['destroy']]);
    }

    /**
     * Muestra la lista de diapositivas y prepara los datos para el formulario.
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obtener los datos necesarios
        $sliders = SliderInicio::all();
        $totalSlides = SliderInicio::count(); // Obtiene el total de slides registrados
        $usedOrders = SliderInicio::pluck('orden')->toArray(); // Órdenes ya utilizadas
        return view('panel-slider-inicio.index', compact('sliders', 'totalSlides', 'usedOrders'));
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
     * Almacena una nueva diapositiva en la base de datos y su imagen en el servidor.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string',
            'descripcion' => 'nullable|string',
            'enlace' => 'nullable|string',
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'orden' => 'required|integer',
            'activo' => 'required|boolean'
        ], [
            'titulo.required' => 'Se requiere un título',
            'titulo.string' => 'El título debe ser un texto',
            'descripcion.string' => 'La descripción debe ser un texto',
            'enlace.string' => 'El enlace debe ser un texto',
            'imagen.required' => 'Se requiere una imagen',
            'imagen.image' => 'La imagen debe ser un archivo de imagen',
            'imagen.mimes' => 'La imagen debe ser un archivo de imagen',
            'imagen.max' => 'La imagen no puede pesar más de 2MB',
            'orden.required' => 'Se requiere un orden',
            'orden.integer' => 'El orden debe ser un número',
            'activo.required' => 'Se requiere un estado de activo',
            'activo.boolean' => 'El estado de activo debe ser un valor booleano'
        ]);

        // Crear una nueva instancia del modelo
        $slide = new SliderInicio();
        $slide->titulo = $request->titulo;
        $slide->descripcion = $request->descripcion;
        $slide->enlace = $request->enlace;
        $slide->orden = $request->orden;
        // Procesar y guardar el archivo del slider si se cargó
        if ($request->hasFile('imagen')) {
            $rutaGuardarSlider = 'img/sliders/';
            $imagen = $request->file('imagen');
            $nombreArchivoSlider = "Slider" . date('YmdHis') . '.' . $imagen->getClientOriginalExtension();

            // Mover el archivo al directorio especificado
            $imagen->move(public_path($rutaGuardarSlider), $nombreArchivoSlider);

            // Guardar el nombre del archivo en la base de datos
            $slide->imagen = $rutaGuardarSlider . $nombreArchivoSlider;
        }
        $slide->save();
        // dd($slide);
        return redirect()->route('panel-slider-inicio.index')->with('success', 'La diapositiva se ha agregado al carrusel correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

   /**
     * Actualiza una diapositiva existente en la base de datos.
     * Si se sube una nueva imagen, reemplaza la anterior.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Comenta o elimina dd() para que la ejecución continúe
        // Log::debug('SliderInicioController@update: Request data:', $request->all()); // Usar Log en lugar de dd() para depurar sin detener

        $slide = SliderInicio::findOrFail($id);
        Log::info("SliderInicioController@update: Actualizando Slider ID: {$id}");

        // Validar los datos del formulario
        // Asegúrate de que los nombres ('titulo', 'orden', 'activo') coincidan
        // con los atributos 'name' de tus campos de formulario en el modal.
        $validatedData = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'enlace' => 'nullable|url', // Es mejor validar como URL si esperas un enlace
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // max 2MB
            'orden' => 'required|integer|min:1', // Asegurar que el orden sea al menos 1
            'activo' => 'required|boolean' // AÑADIDO: Validar el campo 'activo'
        ], [
            'titulo.required' => 'Se requiere un título para la diapositiva.',
            'titulo.string' => 'El título debe ser texto.',
            'titulo.max' => 'El título no debe exceder los 255 caracteres.',
            'descripcion.string' => 'La descripción debe ser texto.',
            'enlace.url' => 'El enlace debe ser una URL válida (ej: http://www.ejemplo.com).',
            'imagen.image' => 'El archivo subido debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe tener uno de los siguientes formatos: jpeg, png, jpg, gif, svg.',
            'imagen.max' => 'La imagen no puede pesar más de 2MB.',
            'orden.required' => 'Se requiere un número de orden.',
            'orden.integer' => 'El orden debe ser un número entero.',
            'orden.min' => 'El orden debe ser al menos 1.',
            'activo.required' => 'Debe especificar si la diapositiva está activa.',
            'activo.boolean' => 'El valor para "activo" no es válido (debe ser verdadero/falso, 1/0).'
        ]);

        Log::debug('SliderInicioController@update: Datos validados:', $validatedData);

        // Actualizar los datos del slide
        $slide->titulo = $validatedData['titulo'];
        $slide->descripcion = $validatedData['descripcion'] ?? null; // Usar null si no se envía
        $slide->enlace = $validatedData['enlace'] ?? null;
        $slide->orden = $validatedData['orden'];
        $slide->activo = $validatedData['activo']; // AÑADIDO: Asignar el valor de 'activo'

        // Si se sube una nueva imagen, eliminar la anterior y guardar la nueva
        if ($request->hasFile('imagen')) {
            Log::debug('SliderInicioController@update: Procesando nueva imagen subida.');
            $rutaGuardarSlider = 'img/sliders/'; // Relativo a public/
            $imagenNueva = $request->file('imagen');
            $nombreArchivoSlider = "Slider_" . $slide->id . "_" . time() . '.' . $imagenNueva->getClientOriginalExtension();

            // Eliminar la imagen anterior si existe
            if ($slide->imagen && file_exists(public_path($slide->imagen))) {
                Log::debug("SliderInicioController@update: Eliminando imagen anterior: {$slide->imagen}");
                unlink(public_path($slide->imagen));
            }

            // Mover la nueva imagen al directorio
            $imagenNueva->move(public_path($rutaGuardarSlider), $nombreArchivoSlider);
            Log::info("SliderInicioController@update: Nueva imagen '{$nombreArchivoSlider}' guardada en '{$rutaGuardarSlider}'.");

            // Guardar la nueva ruta de la imagen
            $slide->imagen = $rutaGuardarSlider . $nombreArchivoSlider;
        } else {
            Log::debug('SliderInicioController@update: No se subió una nueva imagen.');
        }

        // Guardar cambios en la base de datos
        $slide->save();
        Log::info("SliderInicioController@update: Slider ID {$id} actualizado exitosamente en la BD.");

        return redirect()->route('panel-slider-inicio.index')
            ->with('success', 'La diapositiva se ha actualizado correctamente.');
    }

  /**
     * Elimina una diapositiva y su archivo de imagen asociado.
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $slide = SliderInicio::findOrFail($id);

        // Eliminar la imagen asociada si existe
        if ($slide->imagen && file_exists(public_path($slide->imagen))) {
            unlink(public_path($slide->imagen));
        }

        // Eliminar el registro de la base de datos
        $slide->delete();

        return redirect()->route('panel-slider-inicio.index')->with('success', 'La diapositiva se ha eliminado correctamente.');
    }
}
