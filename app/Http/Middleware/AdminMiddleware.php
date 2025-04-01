<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/admin/login');
        }

        if (!Auth::user()->hasRole('admin')) {
            // Si no es admin pero tiene rol de juez, redirigir al panel de jueces
            if (Auth::user()->hasRole('judge')) {
                return redirect('/judge');
            }
            
            // Si no es admin ni juez pero tiene rol de competidor, redirigir al panel de competidores
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
