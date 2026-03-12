<?php

namespace App\Http\Controllers;

use App\Models\CatProgramaDerivadoEspecial;
use App\Models\CatPlanEstatalDesarrollo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CatProgramaDerivadoEspecialController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $programas = CatProgramaDerivadoEspecial::with('catPlanEstatalDesarrollo')->get();
        return view('panel-programas-derivados-especiales.index', compact('programas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $planes = CatPlanEstatalDesarrollo::all();
        $programa = new CatProgramaDerivadoEspecial(); // Instance for the form
        return view('panel-programas-derivados-especiales.form', compact('planes', 'programa'));
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
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'descripcion' => 'required|string',
            'color' => 'required|string|max:7', // Hex color #RRGGBB
            'plan_estatal' => 'required|exists:cat_planes_estatales_desarrollo,id',
            'documento' => 'required|url',
        ]);

        $input = $request->all();

        if ($image = $request->file('imagen')) {
            $destinationPath = 'image/programas-derivados-especiales/';
            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move(public_path($destinationPath), $profileImage);
            $input['imagen'] = "$destinationPath$profileImage";
        }

        CatProgramaDerivadoEspecial::create($input);

        return redirect()->route('panel-cat-prog-der-esp.index')
            ->with('success', 'Programa creado exitosamente.');
    }

    public function edit($id)
    {
        $programa = CatProgramaDerivadoEspecial::findOrFail($id);
        $planes = CatPlanEstatalDesarrollo::all();
        return view('panel-programas-derivados-especiales.form', compact('programa', 'planes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'descripcion' => 'required|string',
            'color' => 'required|string|max:7',
            'plan_estatal' => 'required|exists:cat_planes_estatales_desarrollo,id',
            'documento' => 'required|url',
        ]);

        $programa = CatProgramaDerivadoEspecial::findOrFail($id);
        $input = $request->all();

        if ($image = $request->file('imagen')) {
            // Delete old image if exists
            if ($programa->imagen && file_exists(public_path($programa->imagen))) {
                unlink(public_path($programa->imagen));
            }

            $destinationPath = 'image/programas-derivados-especiales/';
            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move(public_path($destinationPath), $profileImage);
            $input['imagen'] = "$destinationPath$profileImage";
        } else {
            unset($input['imagen']);
        }

        $programa->update($input);

        return redirect()->route('panel-cat-prog-der-esp.index')
            ->with('success', 'Programa actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $programa = CatProgramaDerivadoEspecial::findOrFail($id);

        if ($programa->imagen && file_exists(public_path($programa->imagen))) {
            unlink(public_path($programa->imagen));
        }

        $programa->delete();

        return redirect()->route('panel-cat-prog-der-esp.index')
            ->with('success', 'Programa eliminado exitosamente.');
    }
}
