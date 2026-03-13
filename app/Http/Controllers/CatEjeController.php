<?php

namespace App\Http\Controllers;

use App\Models\CatEje;
use Illuminate\Http\Request;

class CatEjeController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'numero' => 'required|integer',
            'color' => 'nullable|string|max:7',
            'plan_id' => 'required|exists:cat_planes_estatales_desarrollo,id',
        ]);

        CatEje::create($request->all());

        return redirect()->route('panel-cat-planes.edit', $request->plan_id)
            ->with('success', 'Eje creado exitosamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'numero' => 'required|integer',
            'color' => 'nullable|string|max:7',
        ]);

        $eje = CatEje::findOrFail($id);
        $eje->update($request->all());

        return redirect()->route('panel-cat-planes.edit', $eje->plan_id)
            ->with('success', 'Eje actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $eje = CatEje::findOrFail($id);
        $plan_id = $eje->plan_id;
        $eje->delete();

        return redirect()->route('panel-cat-planes.edit', $plan_id)
            ->with('success', 'Eje eliminado exitosamente.');
    }
}
