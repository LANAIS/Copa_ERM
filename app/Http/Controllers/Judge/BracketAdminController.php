<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Models\Llave;
use App\Models\Enfrentamiento;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BracketAdminController extends Controller
{
    /**
     * Muestra la vista de administración de un bracket específico
     */
    public function show($id)
    {
        try {
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
            
            // Obtener rondas únicas
            $rondas = $enfrentamientos->pluck('ronda')->unique()->sort()->values()->toArray();
            
            return view('judge.brackets.admin', [
                'bracket' => $bracket,
                'enfrentamientos' => $enfrentamientos,
                'totalEnfrentamientos' => $totalEnfrentamientos,
                'completados' => $completados,
                'porcentajeCompletado' => $porcentajeCompletado,
                'rondas' => $rondas,
            ]);
        } catch (\Exception $e) {
            return response()->view('errors.custom', [
                'title' => 'Error al cargar el bracket',
                'message' => 'No se pudo cargar el bracket especificado: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtiene los enfrentamientos del bracket con filtros opcionales
     */
    private function getEnfrentamientos(Llave $bracket, $search = '', $estadoFilter = '', $rondaFilter = ''): Collection
    {
        $query = $bracket->enfrentamientos()->with(['equipo1', 'equipo2', 'ganador']);
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('equipo1', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%");
                })->orWhereHas('equipo2', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%");
                });
            });
        }
        
        if ($estadoFilter) {
            $query->where('estado', $estadoFilter);
        }
        
        if ($rondaFilter) {
            $query->where('ronda', $rondaFilter);
        }
        
        return $query->orderBy('ronda', 'asc')
                    ->orderBy('posicion', 'asc')
                    ->get();
    }
    
    /**
     * Cambia el estado del bracket (iniciar, finalizar, reiniciar)
     */
    public function cambiarEstado(Request $request, $id)
    {
        $bracket = Llave::findOrFail($id);
        
        try {
            DB::beginTransaction();
            
            switch ($bracket->estado_torneo) {
                case 'pendiente':
                    $bracket->update(['estado_torneo' => 'en_curso']);
                    $mensaje = 'Torneo iniciado correctamente.';
                    break;
                case 'en_curso':
                    $bracket->update(['estado_torneo' => 'finalizado']);
                    $mensaje = 'Torneo finalizado correctamente.';
                    break;
                case 'finalizado':
                    $this->resetearTodosLosResultados($bracket);
                    $bracket->update(['estado_torneo' => 'pendiente']);
                    $mensaje = 'Torneo reiniciado correctamente.';
                    break;
                default:
                    $mensaje = 'Estado del torneo actualizado.';
            }
            
            DB::commit();
            
            return redirect()->route('judge.brackets.admin', $id)
                ->with('success', $mensaje);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('judge.brackets.admin', $id)
                ->with('error', 'No se pudo cambiar el estado del torneo: ' . $e->getMessage());
        }
    }
    
    /**
     * Resetea todos los resultados del bracket
     */
    private function resetearTodosLosResultados(Llave $bracket)
    {
        // Obtener todos los enfrentamientos
        $enfrentamientos = $bracket->enfrentamientos;
        
        foreach ($enfrentamientos as $enfrentamiento) {
            $enfrentamiento->update([
                'puntuacion_equipo1' => 0,
                'puntuacion_equipo2' => 0,
                'ganador_id' => null,
                'estado' => 'pendiente',
            ]);
        }
    }
    
    /**
     * Guarda los resultados de un enfrentamiento
     */
    public function guardarResultado(Request $request, $id, $enfrentamientoId)
    {
        $bracket = Llave::findOrFail($id);
        $enfrentamiento = Enfrentamiento::with(['equipo1', 'equipo2'])
            ->findOrFail($enfrentamientoId);
            
        // Validar datos
        $request->validate([
            'puntuacion_equipo1' => 'required|integer|min:0',
            'puntuacion_equipo2' => 'required|integer|min:0',
            'ganador_id' => 'required|exists:equipos,id',
        ]);
        
        // Verificar que el ganador es uno de los equipos del enfrentamiento
        if ($enfrentamiento->equipo1_id != $request->ganador_id && 
            $enfrentamiento->equipo2_id != $request->ganador_id) {
            return redirect()->route('judge.brackets.admin', $id)
                ->with('error', 'El ganador seleccionado no es válido.');
        }
        
        try {
            DB::beginTransaction();
            
            // Actualizar enfrentamiento
            $enfrentamiento->update([
                'puntuacion_equipo1' => $request->puntuacion_equipo1,
                'puntuacion_equipo2' => $request->puntuacion_equipo2,
                'ganador_id' => $request->ganador_id,
                'estado' => 'completado',
            ]);
            
            // Actualizar los enfrentamientos siguientes
            $this->actualizarEnfrentamientosSiguientes($bracket, $enfrentamiento);
            
            DB::commit();
            
            return redirect()->route('judge.brackets.admin', $id)
                ->with('success', 'Resultado guardado correctamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('judge.brackets.admin', $id)
                ->with('error', 'No se pudo guardar el resultado: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualiza los enfrentamientos siguientes después de registrar un resultado
     */
    private function actualizarEnfrentamientosSiguientes(Llave $bracket, Enfrentamiento $enfrentamiento)
    {
        // Determinar la ronda y posición actual
        $rondaActual = $enfrentamiento->ronda;
        $posicionActual = $enfrentamiento->posicion;
        
        // Solo actualizar si no es la final
        if ($rondaActual >= $bracket->estructura['total_rondas']) {
            return;
        }
        
        // Calcular la posición en la siguiente ronda
        $posicionSiguiente = ceil($posicionActual / 2);
        
        // Buscar el enfrentamiento de la siguiente ronda
        $siguienteEnfrentamiento = Enfrentamiento::where('llave_id', $bracket->id)
            ->where('ronda', $rondaActual + 1)
            ->where('posicion', $posicionSiguiente)
            ->first();
            
        if (!$siguienteEnfrentamiento) {
            return;
        }
        
        // Determinar si avanza como equipo1 o equipo2
        if ($posicionActual % 2 == 1) {
            // Posición impar avanza como equipo1
            $siguienteEnfrentamiento->equipo1_id = $enfrentamiento->ganador_id;
        } else {
            // Posición par avanza como equipo2
            $siguienteEnfrentamiento->equipo2_id = $enfrentamiento->ganador_id;
        }
        
        $siguienteEnfrentamiento->save();
    }
    
    /**
     * Reinicia los resultados de un enfrentamiento específico
     */
    public function reiniciarResultado(Request $request, $id, $enfrentamientoId)
    {
        $bracket = Llave::findOrFail($id);
        $enfrentamiento = Enfrentamiento::findOrFail($enfrentamientoId);
        
        try {
            DB::beginTransaction();
            
            // Guardar ID del ganador antes de reiniciar
            $ganadorAnteriorId = $enfrentamiento->ganador_id;
            
            // Reiniciar enfrentamiento
            $enfrentamiento->update([
                'puntuacion_equipo1' => 0,
                'puntuacion_equipo2' => 0,
                'ganador_id' => null,
                'estado' => 'pendiente',
            ]);
            
            // Limpiar enfrentamientos siguientes que tengan al ganador anterior
            if ($ganadorAnteriorId) {
                $this->limpiarEnfrentamientosSiguientes($bracket, $enfrentamiento, $ganadorAnteriorId);
            }
            
            DB::commit();
            
            return redirect()->route('judge.brackets.admin', $id)
                ->with('success', 'Resultado reiniciado correctamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('judge.brackets.admin', $id)
                ->with('error', 'No se pudo reiniciar el resultado: ' . $e->getMessage());
        }
    }
    
    /**
     * Limpia los enfrentamientos siguientes que contienen al ganador anterior
     */
    private function limpiarEnfrentamientosSiguientes(Llave $bracket, Enfrentamiento $enfrentamiento, $ganadorId)
    {
        // Determinar ronda y posición
        $rondaActual = $enfrentamiento->ronda;
        $posicionActual = $enfrentamiento->posicion;
        
        // Solo continuar si no es la final
        if ($rondaActual >= $bracket->estructura['total_rondas']) {
            return;
        }
        
        // Calcular posición en la siguiente ronda
        $posicionSiguiente = ceil($posicionActual / 2);
        
        // Buscar enfrentamiento en la siguiente ronda
        $siguienteEnfrentamiento = Enfrentamiento::where('llave_id', $bracket->id)
            ->where('ronda', $rondaActual + 1)
            ->where('posicion', $posicionSiguiente)
            ->first();
            
        if (!$siguienteEnfrentamiento) {
            return;
        }
        
        // Determinar si el ganador estaba como equipo1 o equipo2
        $campoModificado = null;
        if ($siguienteEnfrentamiento->equipo1_id == $ganadorId) {
            $siguienteEnfrentamiento->equipo1_id = null;
            $campoModificado = 'equipo1_id';
        } elseif ($siguienteEnfrentamiento->equipo2_id == $ganadorId) {
            $siguienteEnfrentamiento->equipo2_id = null;
            $campoModificado = 'equipo2_id';
        }
        
        // Solo continuar si se modificó algún campo
        if ($campoModificado) {
            // Si el enfrentamiento tenía ganador, hay que limpiar los siguientes
            if ($siguienteEnfrentamiento->ganador_id) {
                $ganadorSiguienteId = $siguienteEnfrentamiento->ganador_id;
                
                // Reiniciar el enfrentamiento
                $siguienteEnfrentamiento->update([
                    'puntuacion_equipo1' => 0,
                    'puntuacion_equipo2' => 0,
                    'ganador_id' => null,
                    'estado' => 'pendiente'
                ]);
                
                // Limpiar enfrentamientos posteriores
                $this->limpiarEnfrentamientosSiguientes($bracket, $siguienteEnfrentamiento, $ganadorSiguienteId);
            } else {
                // Si no tenía ganador, solo guardar el cambio de equipo
                $siguienteEnfrentamiento->save();
            }
        }
    }
    
    /**
     * Filtra los enfrentamientos según los criterios proporcionados
     */
    public function filtrar(Request $request, $id)
    {
        $bracket = Llave::findOrFail($id);
        
        $search = $request->input('search', '');
        $estadoFilter = $request->input('estado_filter', '');
        $rondaFilter = $request->input('ronda_filter', '');
        
        $enfrentamientos = $this->getEnfrentamientos($bracket, $search, $estadoFilter, $rondaFilter);
        
        $totalEnfrentamientos = $enfrentamientos->count();
        $completados = $enfrentamientos->where('estado', 'completado')->count();
        $porcentajeCompletado = $totalEnfrentamientos > 0 
            ? round(($completados / $totalEnfrentamientos) * 100) 
            : 0;
        
        $rondas = $bracket->enfrentamientos()
            ->pluck('ronda')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
        
        return view('judge.brackets.admin', [
            'bracket' => $bracket,
            'enfrentamientos' => $enfrentamientos,
            'totalEnfrentamientos' => $totalEnfrentamientos,
            'completados' => $completados,
            'porcentajeCompletado' => $porcentajeCompletado,
            'rondas' => $rondas,
            'search' => $search,
            'estadoFilter' => $estadoFilter,
            'rondaFilter' => $rondaFilter,
        ]);
    }
} 