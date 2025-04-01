<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Models\Llave;
use Illuminate\Http\Request;

class BracketViewerController extends Controller
{
    /**
     * Muestra la vista de administración de brackets
     */
    public function adminView($id)
    {
        try {
            // Verificamos que el bracket exista
            $bracket = Llave::findOrFail($id);
            
            // Almacenamos el ID en la sesión para que el componente pueda recuperarlo
            session(['llave_id' => $id, 'bracketRedirect' => $id]);
            
            // Redirigimos a la ruta sin parámetro
            return redirect()->route('filament.judge.pages.bracket-admin');
        } catch (\Exception $e) {
            return response()->view('errors.custom', [
                'title' => 'Error al cargar el bracket', 
                'message' => 'No se pudo cargar el bracket: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Muestra la vista pública de brackets
     */
    public function publicView($id)
    {
        try {
            $bracket = Llave::with(['categoriaEvento.categoria', 'categoriaEvento.evento', 'enfrentamientos.equipo1', 'enfrentamientos.equipo2', 'enfrentamientos.ganador'])->findOrFail($id);
            
            $enfrentamientos = $bracket->enfrentamientos->sortBy([
                ['ronda', 'asc'],
                ['posicion', 'asc']
            ]);
            
            $totalEnfrentamientos = $enfrentamientos->count();
            $completados = $enfrentamientos->where('estado', 'completado')->count();
            $porcentajeCompletado = $totalEnfrentamientos > 0 ? round(($completados / $totalEnfrentamientos) * 100) : 0;
            
            return view('judge.brackets.public', [
                'bracket' => $bracket,
                'enfrentamientos' => $enfrentamientos,
                'totalEnfrentamientos' => $totalEnfrentamientos,
                'completados' => $completados,
                'porcentajeCompletado' => $porcentajeCompletado,
            ]);
        } catch (\Exception $e) {
            return response()->view('errors.custom', [
                'title' => 'Error al cargar el bracket', 
                'message' => 'No se pudo cargar el bracket: ' . $e->getMessage()
            ], 500);
        }
    }
}
