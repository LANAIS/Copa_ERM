<?php

namespace App\Http\Controllers\Admin;

use App\Models\Llave;
use App\Models\Equipo;
use Illuminate\Http\Request;
use App\Models\InscripcionEvento;
use App\Http\Controllers\Controller;

class BracketManagerController extends Controller
{
    /**
     * Mostrar la página para administrar un bracket
     */
    public function show($llaveId)
    {
        $llave = Llave::with(['enfrentamientos.equipo1', 'enfrentamientos.equipo2', 'categoriaEvento.categoria'])
            ->findOrFail($llaveId);
        
        // Obtener equipos disponibles para el torneo (filtrando por los inscritos en esta categoría)
        $equiposInscritos = InscripcionEvento::where('categoria_evento_id', $llave->categoria_evento_id)
            ->where('estado', 'aprobada')
            ->with(['equipo', 'robots'])
            ->get()
            ->pluck('equipo')
            ->filter()
            ->unique('id');
        
        // Si no hay equipos inscritos, mostrar todos los equipos (fallback)
        if ($equiposInscritos->isEmpty()) {
            $equipos = Equipo::all();
        } else {
            $equipos = $equiposInscritos;
        }
        
        return view('brackets.admin-filament', [
            'llave' => $llave,
            'equipos' => $equipos,
            'tiposTorneo' => [
                Llave::TIPO_ELIMINACION_DIRECTA => 'Eliminación Directa (Single Elimination)',
                Llave::TIPO_ELIMINACION_DOBLE => 'Eliminación Doble (Double Elimination)',
                Llave::TIPO_TODOS_CONTRA_TODOS => 'Todos contra Todos (Round Robin)',
                Llave::TIPO_SUIZO => 'Sistema Suizo (Swiss)',
                Llave::TIPO_GRUPOS => 'Fase de Grupos',
                Llave::TIPO_FASE_GRUPOS_ELIMINACION => 'Fase de Grupos + Eliminación',
            ]
        ]);
    }
    
    /**
     * Configurar el tipo de torneo
     */
    public function configurarTipo(Request $request, $llaveId)
    {
        $llave = Llave::findOrFail($llaveId);
        
        $validated = $request->validate([
            'tipo_fixture' => 'required|in:eliminacion_directa,eliminacion_doble,todos_contra_todos,suizo,grupos,fase_grupos_eliminacion',
            'usar_cabezas_serie' => 'boolean',
        ]);
        
        $llave->update([
            'tipo_fixture' => $validated['tipo_fixture'],
            'usar_cabezas_serie' => $request->input('usar_cabezas_serie', false),
        ]);
        
        return redirect()->route('admin.brackets.admin', $llave->id)
            ->with('success', 'Tipo de torneo configurado correctamente');
    }
    
    /**
     * Generar el bracket (fixture)
     */
    public function generarBracket(Request $request, $llaveId)
    {
        $llave = Llave::findOrFail($llaveId);
        
        $validated = $request->validate([
            'equipos' => 'required|array|min:2',
            'equipos.*' => 'exists:equipos,id',
        ]);
        
        $equipos = $validated['equipos'];
        
        // Generar el bracket según el tipo de fixture
        switch ($llave->tipo_fixture) {
            case Llave::TIPO_ELIMINACION_DIRECTA:
                $llave->generarEliminacionDirecta($equipos);
                break;
            case Llave::TIPO_ELIMINACION_DOBLE:
                $llave->generarEliminacionDoble($equipos);
                break;
            case Llave::TIPO_TODOS_CONTRA_TODOS:
                $llave->generarTodosContraTodos($equipos);
                break;
            case Llave::TIPO_SUIZO:
                $rondas = $request->input('rondas', 3);
                $llave->generarSuizo($equipos, $rondas);
                break;
            case Llave::TIPO_GRUPOS:
            case Llave::TIPO_FASE_GRUPOS_ELIMINACION:
                $numGrupos = $request->input('num_grupos', 2);
                $conEliminacion = $llave->tipo_fixture === Llave::TIPO_FASE_GRUPOS_ELIMINACION;
                $llave->generarGrupos($equipos, $numGrupos, $conEliminacion);
                break;
        }
        
        // Actualizar estado
        $llave->iniciar();
        
        // Actualizar estado de la competencia
        $llave->categoriaEvento->update(['estado_competencia' => 'en_curso']);
        
        return redirect()->route('admin.brackets.admin', $llave->id)
            ->with('success', 'Bracket generado correctamente');
    }
    
    /**
     * Iniciar el torneo
     */
    public function iniciar($llaveId)
    {
        $llave = Llave::findOrFail($llaveId);
        $llave->iniciar();
        
        return redirect()->route('admin.brackets.admin', $llave->id)
            ->with('success', 'Torneo iniciado correctamente');
    }
    
    /**
     * Finalizar el torneo
     */
    public function finalizar($llaveId)
    {
        $llave = Llave::findOrFail($llaveId);
        $llave->finalizar();
        
        // Actualizar estado de la competencia
        $llave->categoriaEvento->update(['estado_competencia' => 'finalizada']);
        
        return redirect()->route('admin.brackets.admin', $llave->id)
            ->with('success', 'Torneo finalizado correctamente');
    }
    
    /**
     * Reiniciar el torneo (elimina resultados)
     */
    public function reiniciar($llaveId)
    {
        $llave = Llave::findOrFail($llaveId);
        $llave->reiniciar();
        
        return redirect()->route('admin.brackets.admin', $llave->id)
            ->with('success', 'Torneo reiniciado correctamente');
    }
} 