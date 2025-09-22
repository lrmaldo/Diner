<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        foreach ($roles as $role) {
            // Comprueba tanto el campo role como los roles de Spatie (por redundancia)
            if ($user->role === $role || $user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permiso para acceder a esta página.');
    }
}
