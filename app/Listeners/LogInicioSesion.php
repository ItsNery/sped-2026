<?php

namespace App\Listeners;

// use App\Events\Login;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class LogInicioSesion
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $user = $event->user;
        $usuario = $user->name ?? 'Usuario desconocido';

        // Reset failed login attempts on success
        if ($user instanceof \App\Models\User) {
            $user->failed_login_attempts = 0;
            $user->save();
        }

        // Log to existing logs_cambios
        DB::table('logs_cambios')->insert([
            'usuario' => $usuario,
            'tabla' => 'users',
            'columna' => null,
            'accion' => "El usuario '$usuario' inició sesión",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log to new login_attempts
        DB::table('login_attempts')->insert([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'status' => 'success',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
