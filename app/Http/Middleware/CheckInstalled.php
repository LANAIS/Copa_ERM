<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Si estamos en proceso de instalación, dejar pasar y evitar conexiones a la base de datos
        if (file_exists(storage_path('app/installing')) || $request->is('installer*')) {
            // Configurar memoria y archivo para evitar errores
            config(['database.default' => 'sqlite']);
            config(['database.connections.sqlite.database' => ':memory:']);
            config(['session.driver' => 'file']);
            config(['cache.default' => 'file']);
            
            return $next($request);
        }
        
        // Si ya está completamente instalado
        if (file_exists(storage_path('app/installed.json'))) {
            return $next($request);
        }
        
        // Si no está instalado ni en proceso de instalación, iniciar instalación
        return redirect()->route('installer.welcome');
    }
} 