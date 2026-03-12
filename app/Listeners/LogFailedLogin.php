<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class LogFailedLogin
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(Failed $event)
    {
        $email = $event->credentials['email'] ?? null;
        $user = $event->user;

        // Fortify con authenticateUsing pasa null como user en el evento Failed,
        // así que buscamos al usuario por email directamente.
        if (!$user && $email) {
            $user = User::where('email', $email)->first();
        }

        if ($user instanceof User) {
            $user->increment('failed_login_attempts');
            $user->refresh(); // Recargar para obtener el valor actualizado después del increment

            if ($user->failed_login_attempts >= 3) {
                $user->is_active = false;
                $user->save();

                $this->logAttempt($user->id, $email, 'locked');
                return;
            }

            $this->logAttempt($user->id, $email, 'failure');
        } else {
            // Attempt with non-existent user
            $this->logAttempt(null, $email, 'failure');
        }
    }

    protected function logAttempt($userId, $email, $status)
    {
        DB::table('login_attempts')->insert([
            'user_id' => $userId,
            'email' => $email,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
