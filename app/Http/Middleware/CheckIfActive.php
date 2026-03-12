<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckIfActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        if (auth('web')->check() && !auth('web')->user()->is_active) {
            auth('web')->logout(); // Llama explícitamente al guard de sesión
            return redirect('/login')->withErrors(['email' => 'Tu cuenta está desactivada.']);
        }
        

        return $next($request);
    }
}
