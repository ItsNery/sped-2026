<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoginAttempt;
use Yajra\DataTables\Facades\DataTables;

class LoginAttemptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Reuse permission for logs if needed, or define a new one later
        // $this->middleware('permission:ver-solicitudes-acceso'); 
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = LoginAttempt::with('user')->orderBy('created_at', 'desc');

            return DataTables::of($query)
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('d/m/Y H:i:s');
                })
                ->addColumn('usuario', function ($row) {
                    return $row->user ? $row->user->name : ($row->email ?? 'N/D');
                })
                ->editColumn('status', function ($row) {
                    $badges = [
                        'success' => '<span class="indicator-detail-status indicator-detail-status--validated"><i class="fa-solid fa-check" aria-hidden="true"></i> Exitoso</span>',
                        'failure' => '<span class="indicator-detail-status indicator-detail-status--unavailable"><i class="fa-solid fa-times" aria-hidden="true"></i> Fallido</span>',
                        'locked' => '<span class="indicator-detail-status indicator-detail-status--pending"><i class="fa-solid fa-lock" aria-hidden="true"></i> Bloqueado</span>',
                    ];
                    return $badges[$row->status] ?? $row->status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        return view('panel-login-attempts.index');
    }
}
