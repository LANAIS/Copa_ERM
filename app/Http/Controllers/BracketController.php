<?php

namespace App\Http\Controllers;

use App\Services\BracketGenerator;
use App\Models\Llave;
use App\Models\Enfrentamiento;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class BracketController extends Controller
{
    /**
     * Mostrar el bracket de un torneo
     */
    public function show($llaveId)
    {
        $llave = Llave::with(['enfrentamientos.equipo1', 'enfrentamientos.equipo2', 'enfrentamientos.ganador', 'categoriaEvento.categoria'])
            ->findOrFail($llaveId);
        
        $datos = $this->prepararDatosBracket($llave);
        
        return view('brackets.public-show', [
            'llave' => $llave,
            'datos' => $datos,
        ]);
    }
    
    /**
     * Obtener datos del bracket en formato JSON (para APIs)
     */
    public function datos($llaveId)
    {
        $llave = Llave::with(['enfrentamientos.equipo1', 'enfrentamientos.equipo2', 'enfrentamientos.ganador'])
            ->findOrFail($llaveId);
        
        $datos = $this->prepararDatosBracket($llave);
        
        return response()->json($datos);
    }
    
    /**
     * Obtener datos del bracket
     */
    public function obtenerDatos(Llave $llave)
    {
        return response()->json([
            'llave' => $llave->load([
                'categoriaEvento.categoria', 
                'categoriaEvento.evento',
                'enfrentamientos.equipo1',
                'enfrentamientos.equipo2',
                'enfrentamientos.ganador'
            ])
        ]);
    }
    
    /**
     * Registrar resultado de un enfrentamiento
     */
    public function registrarResultado(Request $request, Enfrentamiento $enfrentamiento)
    {
        try {
            // Validar datos
            $request->validate([
                'puntos_equipo1' => 'required|integer|min:0',
                'puntos_equipo2' => 'required|integer|min:0',
                'ganador_id' => 'required|integer|exists:equipos,id',
            ]);
            
            $puntos1 = $request->input('puntos_equipo1');
            $puntos2 = $request->input('puntos_equipo2');
            $ganadorId = $request->input('ganador_id');
            
            // Verificar que el ganador sea uno de los equipos del enfrentamiento
            if ($ganadorId != $enfrentamiento->equipo1_id && $ganadorId != $enfrentamiento->equipo2_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El ganador debe ser uno de los equipos del enfrentamiento'
                ], 422);
            }
            
            DB::beginTransaction();
            
            // Actualizar enfrentamiento
            $enfrentamiento->update([
                'puntos_equipo1' => $puntos1,
                'puntos_equipo2' => $puntos2,
                'ganador_id' => $ganadorId,
                'completado' => true
            ]);
            
            // Avanzar ganador al siguiente enfrentamiento
            $llave = $enfrentamiento->llave;
            $llave->avanzarGanador($enfrentamiento);
            
            // Verificar si el torneo ha finalizado
            $pendientes = $llave->enfrentamientos()->whereNull('ganador_id')->count();
            
            if ($pendientes == 0) {
                $llave->update([
                    'finalizado' => true,
                    'estado_torneo' => Llave::ESTADO_FINALIZADO
                ]);
            } else if ($llave->estado_torneo == Llave::ESTADO_PENDIENTE) {
                $llave->update([
                    'estado_torneo' => Llave::ESTADO_EN_CURSO
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Resultado registrado con éxito',
                'enfrentamiento' => $enfrentamiento->fresh(['equipo1', 'equipo2', 'ganador']),
                'llave' => $llave->fresh()
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar resultado: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Preparar datos en formato adecuado para el visor de brackets
     */
    private function prepararDatosBracket(Llave $llave)
    {
        $datos = [];
        
        // Información básica del torneo
        $datos['torneo'] = [
            'id' => $llave->id,
            'nombre' => $llave->categoriaEvento->categoria->nombre,
            'tipo' => $llave->tipo_fixture,
            'estado' => $llave->estado_torneo,
            'finalizado' => $llave->finalizado,
            'estructura' => $llave->estructura,
        ];
        
        // Participantes (equipos)
        $participantes = [];
        $equipoIds = $llave->enfrentamientos()
            ->where('ronda', 1) // Solo de primera ronda
            ->whereNotNull('equipo1_id')
            ->orWhereNotNull('equipo2_id')
            ->get()
            ->pluck('equipo1_id')
            ->merge($llave->enfrentamientos()->pluck('equipo2_id'))
            ->filter()
            ->unique()
            ->values();
        
        $equipos = Equipo::whereIn('id', $equipoIds)->get();
        
        foreach ($equipos as $equipo) {
            $participantes[] = [
                'id' => $equipo->id,
                'nombre' => $equipo->nombre,
                'imagen' => $equipo->imagen ?? null,
                'seed' => null, // TODO: Implementar seed si es necesario
            ];
        }
        
        $datos['participantes'] = $participantes;
        
        // Matches (enfrentamientos)
        $matches = [];
        $enfrentamientos = $llave->enfrentamientos()->orderBy('numero_juego')->get();
        
        foreach ($enfrentamientos as $enfrentamiento) {
            $match = [
                'id' => $enfrentamiento->id,
                'numero_juego' => $enfrentamiento->numero_juego,
                'ronda' => $enfrentamiento->ronda,
                'posicion' => $enfrentamiento->posicion,
                'fase' => $enfrentamiento->fase,
                'grupo' => $enfrentamiento->grupo,
                'equipo1_id' => $enfrentamiento->equipo1_id,
                'equipo2_id' => $enfrentamiento->equipo2_id,
                'puntaje_equipo1' => $enfrentamiento->puntaje_equipo1,
                'puntaje_equipo2' => $enfrentamiento->puntaje_equipo2,
                'ganador_id' => $enfrentamiento->ganador_id,
                'estado' => $enfrentamiento->tieneResultado() ? 'completado' : 
                            ($enfrentamiento->enCurso() ? 'en_curso' : 'pendiente'),
            ];
            
            $matches[] = $match;
        }
        
        $datos['matches'] = $matches;
        
        return $datos;
    }

    /**
     * Generar un nuevo bracket
     */
    public function generar(Request $request, Llave $llave)
    {
        try {
            // Validar datos
            $request->validate([
                'tipo_bracket' => 'required|string',
                'equipos' => 'required|array|min:2',
                'equipos.*' => 'required|integer|exists:equipos,id',
                'usar_cabezas_serie' => 'boolean',
                'rondas_suizo' => 'nullable|integer|min:1',
                'num_grupos' => 'nullable|integer|min:1',
                'equipos_clasificados' => 'nullable|integer|min:1',
            ]);
            
            $tipo = $request->input('tipo_bracket');
            $equipos = $request->input('equipos');
            
            // Configurar opciones
            $opciones = [
                'usar_cabezas_serie' => $request->input('usar_cabezas_serie', false),
                'rondas_suizo' => $request->input('rondas_suizo', 0),
                'num_grupos' => $request->input('num_grupos', 4),
                'equipos_clasificados' => $request->input('equipos_clasificados', 2),
            ];
            
            // Crear generador
            $generator = new BracketGenerator($llave);
            $generator->withOptions($opciones);
            
            // Generar bracket
            DB::beginTransaction();
            $success = $generator->generate($equipos, $tipo);
            DB::commit();
            
            return response()->json([
                'success' => $success,
                'message' => 'Bracket generado con éxito',
                'llave_id' => $llave->id,
                'tipo' => $tipo,
                'estructura' => $llave->estructura
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al generar bracket: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reiniciar resultado de un enfrentamiento
     */
    public function reiniciarResultado(Enfrentamiento $enfrentamiento)
    {
        try {
            DB::beginTransaction();
            
            // Obtener la llave
            $llave = $enfrentamiento->llave;
            
            // Actualizar enfrentamiento
            $enfrentamiento->update([
                'puntos_equipo1' => null,
                'puntos_equipo2' => null,
                'ganador_id' => null,
                'completado' => false
            ]);
            
            // Reiniciar enfrentamientos subsecuentes
            $llave->reiniciarEnfrentamientosPosterior($enfrentamiento);
            
            // Actualizar estado de la llave
            $llave->update([
                'finalizado' => false,
                'estado_torneo' => Llave::ESTADO_EN_CURSO
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Resultado reiniciado con éxito',
                'enfrentamiento' => $enfrentamiento->fresh(['equipo1', 'equipo2']),
                'llave' => $llave->fresh()
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al reiniciar resultado: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener próximos enfrentamientos
     */
    public function proximosEnfrentamientos(Llave $llave, $limit = 5)
    {
        $proximosEnfrentamientos = $llave->enfrentamientos()
            ->with(['equipo1', 'equipo2'])
            ->whereNull('ganador_id')
            ->whereNotNull('equipo1_id')
            ->whereNotNull('equipo2_id')
            ->orderBy('fase')
            ->orderBy('ronda')
            ->orderBy('posicion')
            ->limit($limit)
            ->get();
            
        return response()->json([
            'enfrentamientos' => $proximosEnfrentamientos
        ]);
    }
    
    /**
     * Obtener últimos resultados
     */
    public function ultimosResultados(Llave $llave, $limit = 5)
    {
        $ultimosResultados = $llave->enfrentamientos()
            ->with(['equipo1', 'equipo2', 'ganador'])
            ->whereNotNull('ganador_id')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
            
        return response()->json([
            'resultados' => $ultimosResultados
        ]);
    }
    
    /**
     * Obtener tabla de posiciones para grupos o RR
     */
    public function tablaPosiciones(Llave $llave, $grupo = null)
    {
        // Verificar que el tipo sea adecuado para tabla de posiciones
        if (!in_array($llave->tipo_fixture, [
            BracketGenerator::TIPO_TODOS_CONTRA_TODOS,
            BracketGenerator::TIPO_GRUPOS,
            BracketGenerator::TIPO_FASE_GRUPOS_ELIMINACION
        ])) {
            return response()->json([
                'success' => false,
                'message' => 'Este tipo de bracket no utiliza tabla de posiciones'
            ], 422);
        }
        
        // Para grupos, verificar el grupo
        $query = $llave->enfrentamientos()->with(['equipo1', 'equipo2', 'ganador']);
        
        if ($grupo) {
            $query->where('grupo', $grupo);
        } else if ($llave->tipo_fixture !== BracketGenerator::TIPO_TODOS_CONTRA_TODOS) {
            // Si hay grupos pero no se especificó, mostrar todos
            $query->where('fase', BracketGenerator::FASE_GRUPOS);
        }
        
        $enfrentamientos = $query->get();
        
        // Calcular tabla de posiciones
        $tabla = $this->calcularTablaPosiciones($enfrentamientos);
        
        return response()->json([
            'tabla' => $tabla,
            'grupos' => $llave->enfrentamientos()
                ->select('grupo')
                ->where('grupo', '<>', null)
                ->distinct()
                ->pluck('grupo')
                ->toArray()
        ]);
    }
    
    /**
     * Calcular tabla de posiciones
     */
    protected function calcularTablaPosiciones($enfrentamientos)
    {
        $tabla = collect();
        
        // Identificar todos los equipos participantes
        $equiposIds = $enfrentamientos->pluck('equipo1_id')
            ->concat($enfrentamientos->pluck('equipo2_id'))
            ->filter()
            ->unique();
            
        $equipos = Equipo::whereIn('id', $equiposIds)->get();
        
        // Inicializar tabla
        foreach ($equipos as $equipo) {
            $tabla->put($equipo->id, [
                'equipo' => $equipo,
                'puntos' => 0,
                'pj' => 0,
                'pg' => 0,
                'pe' => 0,
                'pp' => 0,
                'gf' => 0,
                'gc' => 0,
                'dif' => 0
            ]);
        }
        
        // Procesar enfrentamientos
        foreach ($enfrentamientos as $enfrentamiento) {
            // Saltar si no tiene ambos equipos
            if (!$enfrentamiento->equipo1_id || !$enfrentamiento->equipo2_id) {
                continue;
            }
            
            $eq1 = $enfrentamiento->equipo1_id;
            $eq2 = $enfrentamiento->equipo2_id;
            
            // Si está completado
            if ($enfrentamiento->ganador_id) {
                // Actualizar partidos jugados
                $tabla[$eq1]['pj']++;
                $tabla[$eq2]['pj']++;
                
                // Actualizar goles/puntos
                $tabla[$eq1]['gf'] += $enfrentamiento->puntos_equipo1;
                $tabla[$eq1]['gc'] += $enfrentamiento->puntos_equipo2;
                $tabla[$eq2]['gf'] += $enfrentamiento->puntos_equipo2;
                $tabla[$eq2]['gc'] += $enfrentamiento->puntos_equipo1;
                
                // Actualizar diferencia
                $tabla[$eq1]['dif'] = $tabla[$eq1]['gf'] - $tabla[$eq1]['gc'];
                $tabla[$eq2]['dif'] = $tabla[$eq2]['gf'] - $tabla[$eq2]['gc'];
                
                // Registrar resultado (ganador/perdedor)
                if ($enfrentamiento->puntos_equipo1 > $enfrentamiento->puntos_equipo2) {
                    // Ganó equipo 1
                    $tabla[$eq1]['pg']++;
                    $tabla[$eq1]['puntos'] += 3;
                    $tabla[$eq2]['pp']++;
                } else if ($enfrentamiento->puntos_equipo1 < $enfrentamiento->puntos_equipo2) {
                    // Ganó equipo 2
                    $tabla[$eq2]['pg']++;
                    $tabla[$eq2]['puntos'] += 3;
                    $tabla[$eq1]['pp']++;
                } else {
                    // Empate
                    $tabla[$eq1]['pe']++;
                    $tabla[$eq2]['pe']++;
                    $tabla[$eq1]['puntos']++;
                    $tabla[$eq2]['puntos']++;
                }
            }
        }
        
        // Ordenar tabla por puntos (desc), luego diferencia, luego goles a favor
        return $tabla->values()->sortByDesc(function ($equipo) {
            return [$equipo['puntos'], $equipo['dif'], $equipo['gf']];
        })->values();
    }
} 