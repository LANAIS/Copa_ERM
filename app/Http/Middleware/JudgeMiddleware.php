<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class JudgeMiddleware
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
            return redirect('/judge/login');
        }

        // Verificar si el usuario tiene rol de juez
        if (!Auth::user()->hasRole('judge')) {
            // Si no es juez pero tiene rol de admin, redirigir al panel de admin
            if (Auth::user()->hasRole('admin')) {
                return redirect('/admin');
            }
            
            // Si no es juez ni admin pero tiene rol de competidor, redirigir al panel de competidores
            if (Auth::user()->hasRole('competitor')) {
                return redirect('/competitor');
            }
            
            // Si no tiene ningún rol, asignar el rol de competidor por defecto y redirigir
            Auth::user()->assignRole('competitor');
            return redirect('/competitor');
        }
        
        return $next($request);
    }
}
