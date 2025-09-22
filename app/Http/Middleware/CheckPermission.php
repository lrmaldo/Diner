<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // Si el usuario es administrador, tiene acceso a todo
        if ($user->isAdmin()) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permiso para realizar esta acción.');
    }
}
