<?php

namespace App\Http\Controllers;

use App\Models\CatPlanEstatalDesarrollo;
use Illuminate\Http\Request;

class CatPlanEstatalDesarrolloController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $planes = CatPlanEstatalDesarrollo::all();
        return view('panel-planes-estatales.index', compact('planes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('panel-planes-estatales.create');
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
            'gobernador' => 'required|string|max:255',
        ]);

        CatPlanEstatalDesarrollo::create($request->all());

        return redirect()->route('panel-cat-planes.index')
            ->with('success', 'Plan estatal creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $plan = CatPlanEstatalDesarrollo::findOrFail($id);
        return view('panel-planes-estatales.edit', compact('plan'));
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
            'gobernador' => 'required|string|max:255',
        ]);

        $plan = CatPlanEstatalDesarrollo::findOrFail($id);
        $plan->update($request->all());

        return redirect()->route('panel-cat-planes.index')
            ->with('success', 'Plan estatal actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $plan = CatPlanEstatalDesarrollo::findOrFail($id);
        $plan->delete();

        return redirect()->route('panel-cat-planes.index')
            ->with('success', 'Plan estatal eliminado exitosamente.');
    }
}
