<?php
// local
namespace App\Http\Controllers;

use App\Models\Institucion;
use Illuminate\Http\Request;

class InstitucionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $instituciones = Institucion::all();
        return view('panel-instituciones.index', compact('instituciones'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Not used, using modal in index
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'titular' => 'required|string|max:255',
        ]);

        Institucion::create($request->all());

        return redirect()->route('panel-cat-instituciones.index')
            ->with('success', 'Institución creada exitosamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Institucion  $institucion
     * @return \Illuminate\Http\Response
     */
    public function show(Institucion $institucion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Institucion  $institucion
     * @return \Illuminate\Http\Response
     */
    public function edit(Institucion $institucion)
    {
        // Not used, using modal in index
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Institucion  $institucion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Institucion $institucion)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'titular' => 'required|string|max:255',
        ]);

        $institucion->update($request->all());

        return redirect()->route('panel-cat-instituciones.index')
            ->with('success', 'Institución actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Institucion  $institucion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Institucion $institucion)
    {
        // 1. Verificamos si tiene registros relacionados
        // Usamos exists() que es más rápido que contar todos los registros
        $tieneDependencias = $institucion->indicadores()->exists() ||
            $institucion->usuario()->exists() ||
            $institucion->usuarios()->exists();

        if ($tieneDependencias) {
            // 2. Si tiene relaciones, regresamos con un error
            return redirect()->route('panel-cat-instituciones.index')
                ->with('error', 'No se puede eliminar la institución porque tiene indicadores o usuarios asociados.');
        }

        // 3. Si está limpio, procedemos a borrar
        $institucion->delete();

        return redirect()->route('panel-cat-instituciones.index')
            ->with('success', 'Institución eliminada exitosamente.');
    }
}
