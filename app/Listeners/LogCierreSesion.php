<?php

namespace App\Listeners;

// use App\Events\Logout;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class LogCierreSesion
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        $usuario = $event->user->name ?? 'Usuario desconocido';

        DB::table('logs_cambios')->insert([
            'usuario' => $usuario,
            'tabla' => 'users', // O la tabla donde guardas usuarios
            'columna' => null,
            'accion' => "El usuario '$usuario' cerró sesión",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
