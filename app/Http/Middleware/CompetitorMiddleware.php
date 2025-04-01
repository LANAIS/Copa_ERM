<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CompetitorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect('/competitor/login');
        }

        // Si el usuario es admin o juez, redirigir a su panel correspondiente
        if (Auth::user()->hasRole('admin')) {
            return redirect('/admin');
        }
        
        if (Auth::user()->hasRole('judge')) {
            return redirect('/judge');
        }
        
        // Si no tiene ningún rol, asignar el rol de competidor por defecto
        if (!Auth::user()->hasRole('competitor')) {
            Auth::user()->assignRole('competitor');
        }
        
        return $next($request);
    }
}
