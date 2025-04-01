<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Models\Llave;
use App\Models\Enfrentamiento;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BracketPublicController extends Controller
{
    /**
     * Muestra la vista pública de un bracket específico
     */
    public function show($id)
    {
        // Obtener el bracket con sus relaciones
        $bracket = Llave::with(['categoriaEvento.categoria', 'categoriaEvento.evento'])
            ->findOrFail($id);
            
        // Obtener enfrentamientos del bracket
        $enfrentamientos = $this->getEnfrentamientos($bracket);
        
        // Calcular estadísticas del torneo
        $totalEnfrentamientos = $enfrentamientos->count();
        $completados = $enfrentamientos->where('estado', 'completado')->count();
        $porcentajeCompletado = $totalEnfrentamientos > 0 
            ? round(($completados / $totalEnfrentamientos) * 100) 
            : 0;
        
        return view('judge.brackets.public', [
            'bracket' => $bracket,
            'enfrentamientos' => $enfrentamientos,
            'totalEnfrentamientos' => $totalEnfrentamientos,
            'completados' => $completados,
            'porcentajeCompletado' => $porcentajeCompletado,
        ]);
    }
    
    /**
     * Obtiene los enfrentamientos del bracket ordenados por ronda y posición
     */
    private function getEnfrentamientos(Llave $bracket): Collection
    {
        return $bracket->enfrentamientos()
            ->with(['equipo1', 'equipo2', 'ganador'])
            ->orderBy('ronda', 'asc')
            ->orderBy('posicion', 'asc')
            ->get();
    }
} 