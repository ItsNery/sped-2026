<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogCambio;
use Illuminate\View\View;

use Yajra\DataTables\Facades\DataTables;

class LogCambioController extends Controller
{
    /**
     * Muestra una lista de todos los registros de cambios (logs).
     *
     * Recupera todos los logs de la base de datos, los ordena por fecha de
     * creación descendente y los pasa a la vista para su visualización.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = LogCambio::query();
            return DataTables::of($query)
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('d/m/Y H:i');
                })
                ->addColumn('columna_display', function ($row) {
                    return $row->columna ?? 'No aplica';
                })
                ->make(true);
        }

        return view('panel-logs.index');
    }
}
