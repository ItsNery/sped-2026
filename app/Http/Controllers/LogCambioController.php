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
            $query = LogCambio::query()
                ->when($request->filled('tabla'), fn ($q) => $q->where('tabla', $request->string('tabla')))
                ->when($request->filled('accion'), fn ($q) => $q->where('accion', $request->string('accion')))
                ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('desde')))
                ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('hasta')));
            return DataTables::of($query)
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('d/m/Y H:i');
                })
                ->addColumn('columna_display', function ($row) {
                    return $row->columna ?? 'No aplica';
                })
                ->editColumn('valor_anterior', fn ($row) => str($row->valor_anterior)->limit(80))
                ->editColumn('valor_nuevo', fn ($row) => str($row->valor_nuevo)->limit(80))
                ->make(true);
        }

        return view('panel-logs.index');
    }
}
