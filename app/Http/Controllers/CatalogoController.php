<?php

namespace App\Http\Controllers;
use App\Models\CatTipo;
use App\Models\CatNivel;
use App\Models\CatDimension;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function getNiveles($tipoId)
    {
        $niveles = CatNivel::where('tipo_id', $tipoId)->get();
        return response()->json($niveles);
    }

    public function getDimensiones($nivelId)
    {
        $dimensiones = CatDimension::where('nivel_id', $nivelId)->get();
        return response()->json($dimensiones);
    }
}
