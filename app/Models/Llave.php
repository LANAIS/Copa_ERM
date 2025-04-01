<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Llave extends Model
{
    use HasFactory;
    
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'llaves';
    
    /**
     * Los atributos que son asignables masivamente.
     */
    protected $fillable = [
        'categoria_evento_id',
        'tipo_fixture',
        'estructura',
        'finalizado',
        'opciones_torneo',
        'estado_torneo',
        'usar_cabezas_serie',
        'evento_id',
        'fecha_id'
    ];
    
    /**
     * Configuración de casting para atributos
     */
    protected $casts = [
        'estructura' => 'json',
        'opciones_torneo' => 'json',
        'finalizado' => 'boolean',
        'usar_cabezas_serie' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Tipos de fixtures
     */
    const TIPO_ELIMINACION_DIRECTA = 'eliminacion_directa';      // Single elimination
    const TIPO_ELIMINACION_DOBLE = 'eliminacion_doble';          // Double elimination
    const TIPO_TODOS_CONTRA_TODOS = 'todos_contra_todos';        // Round Robin
    const TIPO_SUIZO = 'suizo';                                  // Swiss
    const TIPO_GRUPOS = 'grupos';                                // Group Stage
    const TIPO_FASE_GRUPOS_ELIMINACION = 'fase_grupos_eliminacion'; // Group + Elimination
    
    /**
     * Estados del torneo
     */
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EN_CURSO = 'en_curso';
    const ESTADO_PAUSADO = 'pausado';
    const ESTADO_FINALIZADO = 'finalizado';
    
    /**
     * Fases de un torneo
     */
    const FASE_GRUPOS = 'grupos';
    const FASE_WINNERS = 'winners';
    const FASE_LOSERS = 'losers';
    const FASE_FINAL = 'final';
    const FASE_SUIZO = 'suizo';
    const FASE_ELIMINACION = 'eliminacion';
    
    /**
     * Obtener el evento asociado
     */
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }
    
    /**
     * Obtener la fecha asociada
     */
    public function fecha(): BelongsTo
    {
        return $this->belongsTo(FechaEvento::class, 'fecha_id');
    }
    
    /**
     * Obtener la categoría de evento asociada
     */
    public function categoriaEvento(): BelongsTo
    {
        return $this->belongsTo(CategoriaEvento::class);
    }
    
    /**
     * Obtener los enfrentamientos asociados
     */
    public function enfrentamientos(): HasMany
    {
        return $this->hasMany(Enfrentamiento::class);
    }
    
    /**
     * Verificar si el fixture está finalizado
     */
    public function estaFinalizado(): bool
    {
        return $this->estado_torneo === self::ESTADO_FINALIZADO;
    }
    
    /**
     * Verificar si el fixture está en curso
     */
    public function estaEnCurso(): bool
    {
        return $this->estado_torneo === self::ESTADO_EN_CURSO;
    }
    
    /**
     * Marcar como finalizado
     */
    public function finalizar(): void
    {
        $this->update([
            'finalizado' => true,
            'estado_torneo' => self::ESTADO_FINALIZADO
        ]);
    }
    
    /**
     * Iniciar el torneo
     */
    public function iniciar(): void
    {
        $this->update(['estado_torneo' => self::ESTADO_EN_CURSO]);
    }
    
    /**
     * Pausar el torneo
     */
    public function pausar(): void
    {
        $this->update(['estado_torneo' => self::ESTADO_PAUSADO]);
    }
    
    /**
     * Reiniciar el torneo (elimina resultados)
     */
    public function reiniciar(): void
    {
        // Eliminar todos los resultados de los enfrentamientos
        $this->enfrentamientos()->update([
            'ganador_id' => null,
            'puntaje_equipo1' => null,
            'puntaje_equipo2' => null
        ]);
        
        // Volver al estado pendiente
        $this->update([
            'estado_torneo' => self::ESTADO_PENDIENTE,
            'finalizado' => false
        ]);
    }
    
    /**
     * Establecer opciones del torneo
     */
    public function configurarOpciones(array $opciones): void
    {
        $this->update(['opciones_torneo' => $opciones]);
    }
    
    /**
     * Obtener una opción específica del torneo
     */
    public function opcion($clave, $valorPorDefecto = null)
    {
        $opciones = $this->opciones_torneo ?: [];
        return $opciones[$clave] ?? $valorPorDefecto;
    }
    
    /**
     * Generar fixture de tipo eliminación directa (Single elimination)
     */
    public function generarEliminacionDirecta(array $equipos): void
    {
        // Limpiar enfrentamientos existentes
        $this->enfrentamientos()->delete();
        
        // Ordenar equipos por ranking si está disponible
        $equiposOrdenados = $this->ordenarEquiposPorRanking($equipos);
        
        // Calcular número de rondas
        $numEquipos = count($equiposOrdenados);
        $numRondas = ceil(log($numEquipos, 2));
        
        // Crear enfrentamientos de primera ronda
        $enfrentamientos = [];
        $posicion = 1;
        
        for ($i = 0; $i < $numEquipos; $i += 2) {
            $enfrentamiento = new Enfrentamiento([
                'llave_id' => $this->id,
                'ronda' => 1,
                'posicion' => $posicion,
                'estado' => 'pendiente'
            ]);
            
            // Asignar equipos si existen
            if (isset($equiposOrdenados[$i])) {
                $enfrentamiento->equipo1_id = $equiposOrdenados[$i];
            }
            if (isset($equiposOrdenados[$i + 1])) {
                $enfrentamiento->equipo2_id = $equiposOrdenados[$i + 1];
            }
            
            $enfrentamiento->save();
            $enfrentamientos[] = $enfrentamiento;
            $posicion++;
        }
        
        // Crear enfrentamientos de rondas siguientes
        for ($ronda = 2; $ronda <= $numRondas; $ronda++) {
            $enfrentamientosRonda = [];
            $numEnfrentamientos = pow(2, $numRondas - $ronda);
            
            for ($i = 0; $i < $numEnfrentamientos; $i++) {
                $enfrentamiento = new Enfrentamiento([
                    'llave_id' => $this->id,
                    'ronda' => $ronda,
                    'posicion' => $i + 1,
                    'estado' => 'pendiente'
                ]);
                
                // Asignar enfrentamientos anteriores
                if (isset($enfrentamientos[$i * 2])) {
                    $enfrentamiento->enfrentamiento_anterior_1_id = $enfrentamientos[$i * 2]->id;
                }
                if (isset($enfrentamientos[$i * 2 + 1])) {
                    $enfrentamiento->enfrentamiento_anterior_2_id = $enfrentamientos[$i * 2 + 1]->id;
                }
                
                $enfrentamiento->save();
                $enfrentamientosRonda[] = $enfrentamiento;
            }
            
            $enfrentamientos = $enfrentamientosRonda;
        }
        
        // Actualizar el estado de la llave
        $this->update([
            'estado' => 'activa',
            'fecha_inicio' => now(),
            'fecha_fin' => null
        ]);
    }
    
    private function ordenarEquiposPorRanking(array $equipos): array
    {
        // Obtener los equipos con sus rankings
        $equiposConRanking = Equipo::whereIn('id', $equipos)
            ->select('id', 'ranking')
            ->get()
            ->keyBy('id')
            ->toArray();
        
        // Ordenar equipos por ranking (si existe) o aleatoriamente
        usort($equipos, function($a, $b) use ($equiposConRanking) {
            $rankingA = $equiposConRanking[$a]['ranking'] ?? 0;
            $rankingB = $equiposConRanking[$b]['ranking'] ?? 0;
            
            if ($rankingA > 0 && $rankingB > 0) {
                return $rankingA - $rankingB;
            }
            
            return rand(-1, 1);
        });
        
        return $equipos;
    }
    
    /**
     * Generar fixture de tipo eliminación doble (Double elimination)
     */
    public function generarEliminacionDoble(array $equipos): void
    {
        // Limpiar enfrentamientos existentes
        $this->enfrentamientos()->delete();
        
        // Ordenar equipos por ranking si está disponible
        $equiposOrdenados = $this->ordenarEquiposPorRanking($equipos);
        
        // Calcular número de rondas
        $numEquipos = count($equiposOrdenados);
        $numRondas = ceil(log($numEquipos, 2));
        
        // Crear enfrentamientos de primera ronda (Winners Bracket)
        $enfrentamientosWinners = [];
        $posicion = 1;
        
        for ($i = 0; $i < $numEquipos; $i += 2) {
            $enfrentamiento = new Enfrentamiento([
                'llave_id' => $this->id,
                'ronda' => 1,
                'posicion' => $posicion,
                'estado' => 'pendiente',
                'fase' => self::FASE_WINNERS
            ]);
            
            // Asignar equipos si existen
            if (isset($equiposOrdenados[$i])) {
                $enfrentamiento->equipo1_id = $equiposOrdenados[$i];
            }
            if (isset($equiposOrdenados[$i + 1])) {
                $enfrentamiento->equipo2_id = $equiposOrdenados[$i + 1];
            }
            
            $enfrentamiento->save();
            $enfrentamientosWinners[] = $enfrentamiento;
            $posicion++;
        }
        
        // Crear enfrentamientos de rondas siguientes (Winners Bracket)
        for ($ronda = 2; $ronda <= $numRondas; $ronda++) {
            $enfrentamientosRonda = [];
            $numEnfrentamientos = pow(2, $numRondas - $ronda);
            
            for ($i = 0; $i < $numEnfrentamientos; $i++) {
                $enfrentamiento = new Enfrentamiento([
                    'llave_id' => $this->id,
                    'ronda' => $ronda,
                    'posicion' => $i + 1,
                    'estado' => 'pendiente',
                    'fase' => self::FASE_WINNERS
                ]);
                
                // Asignar enfrentamientos anteriores
                if (isset($enfrentamientosWinners[$i * 2])) {
                    $enfrentamiento->enfrentamiento_anterior_1_id = $enfrentamientosWinners[$i * 2]->id;
                }
                if (isset($enfrentamientosWinners[$i * 2 + 1])) {
                    $enfrentamiento->enfrentamiento_anterior_2_id = $enfrentamientosWinners[$i * 2 + 1]->id;
                }
                
                $enfrentamiento->save();
                $enfrentamientosRonda[] = $enfrentamiento;
            }
            
            $enfrentamientosWinners = $enfrentamientosRonda;
        }
        
        // Crear enfrentamientos de primera ronda (Losers Bracket)
        $enfrentamientosLosers = [];
        $posicion = 1;
        
        for ($i = 0; $i < $numEquipos / 2; $i++) {
            $enfrentamiento = new Enfrentamiento([
                'llave_id' => $this->id,
                'ronda' => 1,
                'posicion' => $posicion,
                'estado' => 'pendiente',
                'fase' => self::FASE_LOSERS
            ]);
            
            $enfrentamiento->save();
            $enfrentamientosLosers[] = $enfrentamiento;
            $posicion++;
        }
        
        // Crear enfrentamientos de rondas siguientes (Losers Bracket)
        for ($ronda = 2; $ronda <= $numRondas - 1; $ronda++) {
            $enfrentamientosRonda = [];
            $numEnfrentamientos = pow(2, $numRondas - $ronda - 1);
            
            for ($i = 0; $i < $numEnfrentamientos; $i++) {
                $enfrentamiento = new Enfrentamiento([
                    'llave_id' => $this->id,
                    'ronda' => $ronda,
                    'posicion' => $i + 1,
                    'estado' => 'pendiente',
                    'fase' => self::FASE_LOSERS
                ]);
                
                // Asignar enfrentamientos anteriores
                if (isset($enfrentamientosLosers[$i * 2])) {
                    $enfrentamiento->enfrentamiento_anterior_1_id = $enfrentamientosLosers[$i * 2]->id;
                }
                if (isset($enfrentamientosLosers[$i * 2 + 1])) {
                    $enfrentamiento->enfrentamiento_anterior_2_id = $enfrentamientosLosers[$i * 2 + 1]->id;
                }
                
                $enfrentamiento->save();
                $enfrentamientosRonda[] = $enfrentamiento;
            }
            
            $enfrentamientosLosers = $enfrentamientosRonda;
        }
        
        // Crear enfrentamiento final
        $enfrentamientoFinal = new Enfrentamiento([
            'llave_id' => $this->id,
            'ronda' => $numRondas + 1,
            'posicion' => 1,
            'estado' => 'pendiente',
            'fase' => self::FASE_FINAL
        ]);
        
        // Asignar enfrentamientos anteriores (winners y losers)
        if (!empty($enfrentamientosWinners)) {
            $enfrentamientoFinal->enfrentamiento_anterior_1_id = end($enfrentamientosWinners)->id;
        }
        if (!empty($enfrentamientosLosers)) {
            $enfrentamientoFinal->enfrentamiento_anterior_2_id = end($enfrentamientosLosers)->id;
        }
        
        $enfrentamientoFinal->save();
        
        // Actualizar el estado de la llave
        $this->update([
            'estado' => 'activa',
            'fecha_inicio' => now(),
            'fecha_fin' => null
        ]);
    }
    
    /**
     * Generar fixture de tipo todos contra todos (Round Robin)
     */
    public function generarTodosContraTodos(array $equipos): void
    {
        // Limpiar enfrentamientos existentes
        $this->enfrentamientos()->delete();
        
        // Ordenar equipos por ranking si está disponible
        $equiposOrdenados = $this->ordenarEquiposPorRanking($equipos);
        
        // Calcular número de equipos y rondas
        $numEquipos = count($equiposOrdenados);
        $numRondas = $numEquipos - 1;
        
        // Crear enfrentamientos para cada ronda
        for ($ronda = 1; $ronda <= $numRondas; $ronda++) {
            // Crear enfrentamientos para esta ronda
            for ($i = 0; $i < $numEquipos / 2; $i++) {
                $equipo1Idx = ($ronda + $i) % $numEquipos;
                $equipo2Idx = ($numEquipos - 1 - $i + $ronda) % $numEquipos;
                
                // Evitar que un equipo juegue contra sí mismo
                if ($equipo1Idx === $equipo2Idx) {
                    $equipo2Idx = ($numEquipos - 1) % $numEquipos;
                }
                
                $enfrentamiento = new Enfrentamiento([
                    'llave_id' => $this->id,
                    'ronda' => $ronda,
                    'posicion' => $i + 1,
                    'estado' => 'pendiente',
                    'fase' => self::FASE_GRUPOS
                ]);
                
                // Asignar equipos
                if (isset($equiposOrdenados[$equipo1Idx])) {
                    $enfrentamiento->equipo1_id = $equiposOrdenados[$equipo1Idx];
                }
                if (isset($equiposOrdenados[$equipo2Idx])) {
                    $enfrentamiento->equipo2_id = $equiposOrdenados[$equipo2Idx];
                }
                
                $enfrentamiento->save();
            }
        }
        
        // Actualizar el estado de la llave
        $this->update([
            'estado' => 'activa',
            'fecha_inicio' => now(),
            'fecha_fin' => null
        ]);
    }
    
    /**
     * Generar fixture de fase de grupos (con o sin eliminación posterior)
     * @param array $equipos Arreglo de IDs de equipos
     * @param int $numGrupos Número de grupos a crear
     * @param bool $conEliminacion Si true, crea fase eliminatoria después de grupos
     */
    public function generarFaseGrupos(array $equipos, int $numGrupos, bool $conEliminacion = false): void
    {
        // Limpiar enfrentamientos existentes
        $this->enfrentamientos()->delete();
        
        // Ordenar equipos por ranking si está disponible
        $equiposOrdenados = $this->ordenarEquiposPorRanking($equipos);
        
        // Calcular número de equipos por grupo
        $numEquipos = count($equiposOrdenados);
        $equiposPorGrupo = ceil($numEquipos / $numGrupos);
        
        // Distribuir equipos en grupos
        $grupos = [];
        for ($i = 0; $i < $numEquipos; $i++) {
            $grupo = floor($i / $equiposPorGrupo) + 1;
            if (!isset($grupos[$grupo])) {
                $grupos[$grupo] = [];
            }
            $grupos[$grupo][] = $equiposOrdenados[$i];
        }
        
        // Crear enfrentamientos para cada grupo
        foreach ($grupos as $grupo => $equiposGrupo) {
            // Si hay un número impar de equipos en el grupo, agregar un "bye"
            if (count($equiposGrupo) % 2 != 0) {
                $equiposGrupo[] = null;
            }
            
            // Crear enfrentamientos para cada ronda
            $numRondas = count($equiposGrupo) - 1;
            for ($ronda = 1; $ronda <= $numRondas; $ronda++) {
                // Crear enfrentamientos para esta ronda
                for ($i = 0; $i < count($equiposGrupo) / 2; $i++) {
                    $equipo1Idx = ($ronda + $i) % count($equiposGrupo);
                    $equipo2Idx = (count($equiposGrupo) - 1 - $i + $ronda) % count($equiposGrupo);
                    
                    // Evitar que un equipo juegue contra sí mismo
                    if ($equipo1Idx === $equipo2Idx) {
                        $equipo2Idx = (count($equiposGrupo) - 1) % count($equiposGrupo);
                    }
                    
                    $enfrentamiento = new Enfrentamiento([
                        'llave_id' => $this->id,
                        'ronda' => $ronda,
                        'posicion' => $i + 1,
                        'estado' => 'pendiente',
                        'fase' => self::FASE_GRUPOS,
                        'grupo' => $grupo
                    ]);
                    
                    // Asignar equipos
                    if (isset($equiposGrupo[$equipo1Idx])) {
                        $enfrentamiento->equipo1_id = $equiposGrupo[$equipo1Idx];
                    }
                    if (isset($equiposGrupo[$equipo2Idx])) {
                        $enfrentamiento->equipo2_id = $equiposGrupo[$equipo2Idx];
                    }
                    
                    $enfrentamiento->save();
                }
            }
        }
        
        // Si se requiere fase de eliminación, crear los enfrentamientos de octavos
        if ($conEliminacion) {
            $this->generarFaseEliminacion($grupos);
        }
        
        // Actualizar el estado de la llave
        $this->update([
            'estado' => 'activa',
            'fecha_inicio' => now(),
            'fecha_fin' => null
        ]);
    }
    
    private function generarFaseEliminacion(array $grupos): void
    {
        // Calcular número de equipos que pasan a octavos (2 por grupo)
        $equiposOctavos = [];
        foreach ($grupos as $grupo => $equiposGrupo) {
            // Obtener los 2 primeros equipos de cada grupo
            $equiposOrdenados = $this->ordenarEquiposPorPuntosGrupo($equiposGrupo, $grupo);
            $equiposOctavos = array_merge($equiposOctavos, array_slice($equiposOrdenados, 0, 2));
        }
        
        // Crear enfrentamientos de octavos
        $numEquipos = count($equiposOctavos);
        $numRondas = ceil(log($numEquipos, 2));
        
        // Crear enfrentamientos de primera ronda (octavos)
        $enfrentamientos = [];
        $posicion = 1;
        
        for ($i = 0; $i < $numEquipos; $i += 2) {
            $enfrentamiento = new Enfrentamiento([
                'llave_id' => $this->id,
                'ronda' => $numRondas + 1, // Continuar desde después de la fase de grupos
                'posicion' => $posicion,
                'estado' => 'pendiente',
                'fase' => self::FASE_ELIMINACION
            ]);
            
            // Asignar equipos
            if (isset($equiposOctavos[$i])) {
                $enfrentamiento->equipo1_id = $equiposOctavos[$i];
            }
            if (isset($equiposOctavos[$i + 1])) {
                $enfrentamiento->equipo2_id = $equiposOctavos[$i + 1];
            }
            
            $enfrentamiento->save();
            $enfrentamientos[] = $enfrentamiento;
            $posicion++;
        }
        
        // Crear enfrentamientos de rondas siguientes
        for ($ronda = $numRondas + 2; $ronda <= $numRondas * 2; $ronda++) {
            $enfrentamientosRonda = [];
            $numEnfrentamientos = pow(2, $numRondas * 2 - $ronda);
            
            for ($i = 0; $i < $numEnfrentamientos; $i++) {
                $enfrentamiento = new Enfrentamiento([
                    'llave_id' => $this->id,
                    'ronda' => $ronda,
                    'posicion' => $i + 1,
                    'estado' => 'pendiente',
                    'fase' => self::FASE_ELIMINACION
                ]);
                
                // Asignar enfrentamientos anteriores
                if (isset($enfrentamientos[$i * 2])) {
                    $enfrentamiento->enfrentamiento_anterior_1_id = $enfrentamientos[$i * 2]->id;
                }
                if (isset($enfrentamientos[$i * 2 + 1])) {
                    $enfrentamiento->enfrentamiento_anterior_2_id = $enfrentamientos[$i * 2 + 1]->id;
                }
                
                $enfrentamiento->save();
                $enfrentamientosRonda[] = $enfrentamiento;
            }
            
            $enfrentamientos = $enfrentamientosRonda;
        }
    }
    
    private function ordenarEquiposPorPuntosGrupo(array $equipos, int $grupo): array
    {
        // Obtener los puntos de cada equipo en el grupo
        $puntos = [];
        foreach ($equipos as $equipoId) {
            if ($equipoId === null) continue;
            
            $puntos[$equipoId] = $this->enfrentamientos()
                ->where(function ($query) use ($equipoId) {
                    $query->where('equipo1_id', $equipoId)
                        ->orWhere('equipo2_id', $equipoId);
                })
                ->where('grupo', $grupo)
                ->where('estado', 'completado')
                ->get()
                ->sum(function ($enfrentamiento) use ($equipoId) {
                    if ($enfrentamiento->ganador_id === $equipoId) return 3;
                    if ($enfrentamiento->ganador_id === null) return 1;
                    return 0;
                });
        }
        
        // Ordenar equipos por puntos (y por ranking en caso de empate)
        usort($equipos, function($a, $b) use ($puntos) {
            if ($a === null) return 1;
            if ($b === null) return -1;
            
            $puntosA = $puntos[$a] ?? 0;
            $puntosB = $puntos[$b] ?? 0;
            
            if ($puntosA !== $puntosB) {
                return $puntosB - $puntosA;
            }
            
            // En caso de empate, usar el ranking
            $rankingA = Equipo::find($a)->ranking ?? 0;
            $rankingB = Equipo::find($b)->ranking ?? 0;
            
            return $rankingB - $rankingA;
        });
        
        return $equipos;
    }
    
    /**
     * Generar fixture de tipo suizo (Swiss System)
     */
    public function generarSuizo(array $equipos, int $numRondas): void
    {
        // Limpiar enfrentamientos existentes
        $this->enfrentamientos()->delete();
        
        // Ordenar equipos por ranking si está disponible
        $equiposOrdenados = $this->ordenarEquiposPorRanking($equipos);
        
        // Calcular número de equipos
        $numEquipos = count($equiposOrdenados);
        
        // Si el número de equipos es impar, agregar un "bye" (descanso)
        if ($numEquipos % 2 != 0) {
            $equiposOrdenados[] = null;
            $numEquipos++;
        }
        
        // Crear enfrentamientos para cada ronda
        for ($ronda = 1; $ronda <= $numRondas; $ronda++) {
            // Ordenar equipos por puntos (en la primera ronda se usa el ranking)
            if ($ronda > 1) {
                $equiposOrdenados = $this->ordenarEquiposPorPuntos($equiposOrdenados, $ronda - 1);
            }
            
            // Crear enfrentamientos para esta ronda
            for ($i = 0; $i < $numEquipos / 2; $i++) {
                $equipo1Idx = $i * 2;
                $equipo2Idx = $i * 2 + 1;
                
                $enfrentamiento = new Enfrentamiento([
                    'llave_id' => $this->id,
                    'ronda' => $ronda,
                    'posicion' => $i + 1,
                    'estado' => 'pendiente',
                    'fase' => self::FASE_SUIZO
                ]);
                
                // Asignar equipos
                if (isset($equiposOrdenados[$equipo1Idx])) {
                    $enfrentamiento->equipo1_id = $equiposOrdenados[$equipo1Idx];
                }
                if (isset($equiposOrdenados[$equipo2Idx])) {
                    $enfrentamiento->equipo2_id = $equiposOrdenados[$equipo2Idx];
                }
                
                $enfrentamiento->save();
            }
        }
        
        // Actualizar el estado de la llave
        $this->update([
            'estado' => 'activa',
            'fecha_inicio' => now(),
            'fecha_fin' => null
        ]);
    }
    
    private function ordenarEquiposPorPuntos(array $equipos, int $ronda): array
    {
        // Obtener los puntos de cada equipo hasta la ronda especificada
        $puntos = [];
        foreach ($equipos as $equipoId) {
            if ($equipoId === null) continue;
            
            $puntos[$equipoId] = $this->enfrentamientos()
                ->where(function ($query) use ($equipoId) {
                    $query->where('equipo1_id', $equipoId)
                        ->orWhere('equipo2_id', $equipoId);
                })
                ->where('ronda', '<=', $ronda)
                ->where('estado', 'completado')
                ->get()
                ->sum(function ($enfrentamiento) use ($equipoId) {
                    if ($enfrentamiento->ganador_id === $equipoId) return 3;
                    if ($enfrentamiento->ganador_id === null) return 1;
                    return 0;
                });
        }
        
        // Ordenar equipos por puntos (y por ranking en caso de empate)
        usort($equipos, function($a, $b) use ($puntos) {
            if ($a === null) return 1;
            if ($b === null) return -1;
            
            $puntosA = $puntos[$a] ?? 0;
            $puntosB = $puntos[$b] ?? 0;
            
            if ($puntosA !== $puntosB) {
                return $puntosB - $puntosA;
            }
            
            // En caso de empate, usar el ranking
            $rankingA = Equipo::find($a)->ranking ?? 0;
            $rankingB = Equipo::find($b)->ranking ?? 0;
            
            return $rankingB - $rankingA;
        });
        
        return $equipos;
    }
    
    /**
     * Obtener los resultados de un grupo específico
     */
    public function obtenerResultadosGrupo(string $grupo): array
    {
        $resultados = [];
        
        // Obtener todos los enfrentamientos del grupo
        $enfrentamientos = $this->enfrentamientos()
            ->where('grupo', $grupo)
            ->where('fase', self::FASE_GRUPOS)
            ->get();
        
        // Obtener todos los equipos del grupo
        $equiposIds = $enfrentamientos->pluck('equipo1_id')
            ->concat($enfrentamientos->pluck('equipo2_id'))
            ->filter()
            ->unique()
            ->values();
        
        // Inicializar estadísticas para cada equipo
        foreach ($equiposIds as $equipoId) {
            $resultados[$equipoId] = [
                'equipo_id' => $equipoId,
                'puntos' => 0,
                'jugados' => 0,
                'ganados' => 0,
                'perdidos' => 0,
                'empatados' => 0,
                'goles_favor' => 0,
                'goles_contra' => 0,
                'diferencia_goles' => 0
            ];
        }
        
        // Calcular estadísticas basadas en los enfrentamientos
        foreach ($enfrentamientos as $enfrentamiento) {
            // Solo procesar enfrentamientos con equipos reales
            if (!$enfrentamiento->equipo1_id || !$enfrentamiento->equipo2_id) {
                continue;
            }
            
            // Obtener IDs de los equipos
            $equipo1Id = $enfrentamiento->equipo1_id;
            $equipo2Id = $enfrentamiento->equipo2_id;
            
            // Si hay un ganador, actualizar estadísticas
            if ($enfrentamiento->ganador_id) {
                // Actualizar jugados para ambos equipos
                $resultados[$equipo1Id]['jugados']++;
                $resultados[$equipo2Id]['jugados']++;
                
                // Actualizar goles
                $resultados[$equipo1Id]['goles_favor'] += $enfrentamiento->puntaje_equipo1 ?: 0;
                $resultados[$equipo1Id]['goles_contra'] += $enfrentamiento->puntaje_equipo2 ?: 0;
                $resultados[$equipo2Id]['goles_favor'] += $enfrentamiento->puntaje_equipo2 ?: 0;
                $resultados[$equipo2Id]['goles_contra'] += $enfrentamiento->puntaje_equipo1 ?: 0;
                
                // Actualizar diferencia de goles
                $resultados[$equipo1Id]['diferencia_goles'] = 
                    $resultados[$equipo1Id]['goles_favor'] - $resultados[$equipo1Id]['goles_contra'];
                $resultados[$equipo2Id]['diferencia_goles'] = 
                    $resultados[$equipo2Id]['goles_favor'] - $resultados[$equipo2Id]['goles_contra'];
                
                // Si hay empate (ganador_id pero puntajes iguales)
                if ($enfrentamiento->puntaje_equipo1 == $enfrentamiento->puntaje_equipo2) {
                    $resultados[$equipo1Id]['empatados']++;
                    $resultados[$equipo2Id]['empatados']++;
                    
                    // Agregar puntos por empate (1 punto)
                    $resultados[$equipo1Id]['puntos'] += 1;
                    $resultados[$equipo2Id]['puntos'] += 1;
                } 
                // Si hay un ganador claro
                else {
                    // Actualizar ganados/perdidos
                    if ($enfrentamiento->ganador_id == $equipo1Id) {
                        $resultados[$equipo1Id]['ganados']++;
                        $resultados[$equipo2Id]['perdidos']++;
                        
                        // Agregar puntos por victoria (3 puntos)
                        $resultados[$equipo1Id]['puntos'] += 3;
                    } else {
                        $resultados[$equipo2Id]['ganados']++;
                        $resultados[$equipo1Id]['perdidos']++;
                        
                        // Agregar puntos por victoria (3 puntos)
                        $resultados[$equipo2Id]['puntos'] += 3;
                    }
                }
            }
        }
        
        // Convertir a array y ordenar
        $resultadosArray = array_values($resultados);
        
        // Ordenar por puntos (desc), diferencia de goles (desc), goles a favor (desc)
        usort($resultadosArray, function($a, $b) {
            // Ordenar por puntos
            if ($a['puntos'] != $b['puntos']) {
                return $b['puntos'] - $a['puntos'];
            }
            
            // Desempate por diferencia de goles
            if ($a['diferencia_goles'] != $b['diferencia_goles']) {
                return $b['diferencia_goles'] - $a['diferencia_goles'];
            }
            
            // Desempate por goles a favor
            return $b['goles_favor'] - $a['goles_favor'];
        });
        
        return $resultadosArray;
    }
    
    /**
     * Configurar la fase eliminatoria con los ganadores de cada grupo
     */
    public function configurarFaseEliminatoria(): bool
    {
        // Solo aplicable a torneos con fase de grupos + eliminación
        if ($this->tipo_fixture !== self::TIPO_FASE_GRUPOS_ELIMINACION) {
            return false;
        }
        
        $estructura = $this->estructura;
        $numGrupos = $estructura['num_grupos'];
        $equiposPorGrupo = $estructura['equipos_por_grupo_avanzan'];
        
        // Verificar que todos los enfrentamientos de grupos tengan resultado
        $pendientes = $this->enfrentamientos()
            ->where('fase', self::FASE_GRUPOS)
            ->whereNull('ganador_id')
            ->count();
        
        if ($pendientes > 0) {
            return false; // Aún hay enfrentamientos pendientes
        }
        
        // Obtener resultados de cada grupo
        $clasificados = [];
        
        for ($i = 0; $i < $numGrupos; $i++) {
            $letraGrupo = chr(65 + $i); // A, B, C, D, etc.
            $resultadosGrupo = $this->obtenerResultadosGrupo($letraGrupo);
            
            // Tomar los primeros N equipos de cada grupo
            for ($j = 0; $j < $equiposPorGrupo && $j < count($resultadosGrupo); $j++) {
                $clasificados[] = [
                    'equipo_id' => $resultadosGrupo[$j]['equipo_id'],
                    'grupo' => $letraGrupo,
                    'posicion' => $j + 1
                ];
            }
        }
        
        // Organizar los enfrentamientos según el algoritmo de Challonge
        // Los primeros de grupo deben enfrentarse con segundos de otro grupo
        // y deben estar lo más lejos posible en el bracket
        
        // Obtener enfrentamientos de la primera ronda de eliminación
        $enfrentamientosPrimeraRonda = $this->enfrentamientos()
            ->where('ronda', 1)
            ->where('fase', self::FASE_WINNERS)
            ->orderBy('posicion')
            ->get();
        
        // Asignar equipos a los enfrentamientos
        for ($i = 0; $i < count($clasificados) && $i / 2 < count($enfrentamientosPrimeraRonda); $i += 2) {
            $enfrentamiento = $enfrentamientosPrimeraRonda[$i / 2];
            
            // Asignar el primer clasificado como equipo1 y el segundo como equipo2
            if (isset($clasificados[$i]) && isset($clasificados[$i + 1])) {
                $enfrentamiento->update([
                    'equipo1_id' => $clasificados[$i]['equipo_id'],
                    'equipo2_id' => $clasificados[$i + 1]['equipo_id']
                ]);
            }
        }
        
        return true;
    }
    
    /**
     * Aplicar cabezas de serie (seeding) al bracket
     */
    private function aplicarSeeding(array $equipos, int $tamanoLlave): array
    {
        $resultado = array_fill(0, $tamanoLlave, null);
        $totalEquipos = count($equipos);
        
        // Si no hay equipos suficientes, rellenar con nulos
        if ($totalEquipos < $tamanoLlave) {
            // Crear un arreglo de posiciones según el algoritmo de seeding
            $posiciones = $this->calcularPosicionesSeeding($tamanoLlave);
            
            // Asignar equipos a posiciones según el seeding
            for ($i = 0; $i < $totalEquipos; $i++) {
                $resultado[$posiciones[$i]] = $equipos[$i];
            }
        } else {
            // Si hay exactamente el número correcto de equipos
            $posiciones = $this->calcularPosicionesSeeding($tamanoLlave);
            
            for ($i = 0; $i < $tamanoLlave; $i++) {
                $resultado[$posiciones[$i]] = $equipos[$i];
            }
        }
        
        return $resultado;
    }
    
    /**
     * Calcular posiciones de seeding según algoritmo de Challonge
     */
    private function calcularPosicionesSeeding(int $tamanoLlave): array
    {
        // Implementar algoritmo de seeding similar a Challonge
        // Este algoritmo coloca a los mejores cabezas de serie lo más lejos posible entre sí
        
        $posiciones = [];
        
        // Para una llave de tamaño potencia de 2, usamos distribución balanceada
        for ($i = 1; $i <= $tamanoLlave; $i++) {
            $posiciones[] = $this->calcularPosicionSeeding($i, $tamanoLlave);
        }
        
        return $posiciones;
    }
    
    /**
     * Calcular posición de un equipo según su seed
     */
    private function calcularPosicionSeeding(int $seed, int $tamanoLlave): int
    {
        // Algoritmo para distribuir según seeding, similar al de Challonge
        if ($seed <= 2) {
            return $seed == 1 ? 0 : $tamanoLlave - 1;
        }
        
        $grupo = (int) ceil(log($seed, 2));
        $posicionEnGrupo = $seed - pow(2, $grupo - 1);
        $tamanoGrupo = pow(2, $grupo);
        $totalGrupos = $tamanoLlave / $tamanoGrupo;
        
        // Calcular posición alternando para separar los cabezas de serie
        if ($posicionEnGrupo % 2 == 0) {
            return (int) ($posicionEnGrupo / 2 * $totalGrupos);
        } else {
            return (int) ($tamanoLlave - 1 - (($posicionEnGrupo - 1) / 2 * $totalGrupos));
        }
    }
    
    /**
     * Avanzar equipos con "bye" (pase automático) a las siguientes rondas
     */
    protected function avanzarByesIniciales(): void
    {
        // Conseguir todos los enfrentamientos de la primera ronda
        $enfrentamientosPrimeraRonda = $this->enfrentamientos()
            ->where('ronda', 1)
            ->where('fase', self::FASE_WINNERS)
            ->get();
        
        foreach ($enfrentamientosPrimeraRonda as $enfrentamiento) {
            // Si es un bye (uno de los equipos es nulo), avanzar al otro equipo
            if (is_null($enfrentamiento->equipo1_id) && !is_null($enfrentamiento->equipo2_id)) {
                $enfrentamiento->update(['ganador_id' => $enfrentamiento->equipo2_id]);
                $this->avanzarGanador($enfrentamiento);
            } elseif (!is_null($enfrentamiento->equipo1_id) && is_null($enfrentamiento->equipo2_id)) {
                $enfrentamiento->update(['ganador_id' => $enfrentamiento->equipo1_id]);
                $this->avanzarGanador($enfrentamiento);
            }
        }
    }
    
    /**
     * Verificar si todos los enfrentamientos tienen resultados
     */
    public function todosEnfrentamientosCompletados(): bool
    {
        return $this->enfrentamientos()->whereNull('ganador_id')->count() === 0;
    }
    
    /**
     * Generar próxima ronda de sistema suizo
     */
    public function generarProximaRondaSuizo(): bool
    {
        if ($this->tipo_fixture !== self::TIPO_SUIZO) {
            return false;
        }
        
        $estructura = $this->estructura;
        $rondaActual = $estructura['ronda_actual'];
        $totalRondas = $estructura['rondas_programadas'];
        
        // Verificar si ya se completaron todas las rondas programadas
        if ($rondaActual >= $totalRondas) {
            return false;
        }
        
        // Verificar si todos los enfrentamientos de la ronda actual están completados
        if (!$this->enfrentamientosRondaCompletados($rondaActual)) {
            return false;
        }
        
        // Obtener equipos y sus puntuaciones
        $equiposConPuntos = $this->obtenerEquiposConPuntosSuizo();
        
        // Ordenar por puntos (de mayor a menor)
        usort($equiposConPuntos, function($a, $b) {
            return $b['puntos'] - $a['puntos'];
        });
        
        // Agrupar equipos por puntos
        $gruposPorPuntos = [];
        foreach ($equiposConPuntos as $equipo) {
            $puntos = $equipo['puntos'];
            if (!isset($gruposPorPuntos[$puntos])) {
                $gruposPorPuntos[$puntos] = [];
            }
            $gruposPorPuntos[$puntos][] = $equipo;
        }
        
        // Generar enfrentamientos para la próxima ronda
        $nuevaRonda = $rondaActual + 1;
        $posicion = 1;
        $numeroJuego = $estructura['total_enfrentamientos_actuales'] + 1;
        $enfrentamientosCreados = [];
        $equiposAsignados = [];
        
        // Para cada grupo de puntos (de mayor a menor)
        foreach ($gruposPorPuntos as $puntos => $equipos) {
            // Mezclar aleatoriamente dentro del grupo para evitar predecibilidad
            shuffle($equipos);
            
            for ($i = 0; $i < count($equipos); $i++) {
                // Saltar equipos ya asignados
                if (in_array($equipos[$i]['equipo_id'], $equiposAsignados)) {
                    continue;
                }
                
                $equipoActual = $equipos[$i]['equipo_id'];
                $equiposAsignados[] = $equipoActual;
                
                // Buscar un oponente que no haya enfrentado antes
                $oponenteId = null;
                
                // Intentar primero con equipos del mismo grupo de puntos
                for ($j = $i + 1; $j < count($equipos); $j++) {
                    $posibleOponente = $equipos[$j]['equipo_id'];
                    if (!in_array($posibleOponente, $equiposAsignados) && 
                        !$this->yaSeEnfrentaron($equipoActual, $posibleOponente)) {
                        $oponenteId = $posibleOponente;
                        $equiposAsignados[] = $oponenteId;
                        break;
                    }
                }
                
                // Si no hay oponente del mismo grupo, buscar en grupos inferiores
                if (is_null($oponenteId)) {
                    foreach ($gruposPorPuntos as $puntosInferiores => $equiposInferiores) {
                        if ($puntosInferiores >= $puntos) continue;
                        
                        foreach ($equiposInferiores as $equipoInferior) {
                            if (!in_array($equipoInferior['equipo_id'], $equiposAsignados) && 
                                !$this->yaSeEnfrentaron($equipoActual, $equipoInferior['equipo_id'])) {
                                $oponenteId = $equipoInferior['equipo_id'];
                                $equiposAsignados[] = $oponenteId;
                                break 2;
                            }
                        }
                    }
                }
                
                // Si aún no hay oponente, asignar cualquier equipo no emparejado
                // aunque se hayan enfrentado antes
                if (is_null($oponenteId)) {
                    foreach ($equiposConPuntos as $posibleEquipo) {
                        if (!in_array($posibleEquipo['equipo_id'], $equiposAsignados)) {
                            $oponenteId = $posibleEquipo['equipo_id'];
                            $equiposAsignados[] = $oponenteId;
                            break;
                        }
                    }
                }
                
                // Crear el enfrentamiento si hemos encontrado un oponente
                if ($oponenteId) {
                    $enfrentamientosCreados[] = [
                        'ronda' => $nuevaRonda,
                        'posicion' => $posicion++,
                        'numero_juego' => $numeroJuego++,
                        'equipo1_id' => $equipoActual,
                        'equipo2_id' => $oponenteId
                    ];
                } else {
                    // Si no hay oponente disponible, el equipo recibe un "bye"
                    $enfrentamientosCreados[] = [
                        'ronda' => $nuevaRonda,
                        'posicion' => $posicion++,
                        'numero_juego' => $numeroJuego++,
                        'equipo1_id' => $equipoActual,
                        'equipo2_id' => null,
                        'ganador_id' => $equipoActual
                    ];
                }
            }
        }
        
        // Crear los enfrentamientos en la base de datos
        foreach ($enfrentamientosCreados as $enfrentamiento) {
            $this->enfrentamientos()->create($enfrentamiento);
        }
        
        // Actualizar estructura
        $estructura['ronda_actual'] = $nuevaRonda;
        $estructura['total_enfrentamientos_actuales'] = $numeroJuego - 1;
        $this->update(['estructura' => $estructura]);
        
        return true;
    }
    
    /**
     * Verificar si los enfrentamientos de una ronda específica están completos
     */
    protected function enfrentamientosRondaCompletados(int $ronda): bool
    {
        return $this->enfrentamientos()
            ->where('ronda', $ronda)
            ->whereNull('ganador_id')
            ->count() === 0;
    }
    
    /**
     * Verificar si dos equipos ya se enfrentaron
     */
    protected function yaSeEnfrentaron($equipo1Id, $equipo2Id): bool
    {
        return $this->enfrentamientos()
            ->where(function($query) use ($equipo1Id, $equipo2Id) {
                $query->where(function($q) use ($equipo1Id, $equipo2Id) {
                    $q->where('equipo1_id', $equipo1Id)
                      ->where('equipo2_id', $equipo2Id);
                })->orWhere(function($q) use ($equipo1Id, $equipo2Id) {
                    $q->where('equipo1_id', $equipo2Id)
                      ->where('equipo2_id', $equipo1Id);
                });
            })
            ->exists();
    }
    
    /**
     * Obtener equipos con sus puntos para sistema suizo
     */
    protected function obtenerEquiposConPuntosSuizo(): array
    {
        $equiposConPuntos = [];
        $equipoIds = $this->equipos()->pluck('equipo_id');
        
        foreach ($equipoIds as $equipoId) {
            // Contar victorias como ganador
            $victorias = $this->enfrentamientos()
                ->where('ganador_id', $equipoId)
                ->count();
            
            // En sistema suizo también podemos agregar desempates
            // como Buchholz (suma de puntos de oponentes) si es necesario
            $equiposConPuntos[] = [
                'equipo_id' => $equipoId,
                'puntos' => $victorias
            ];
        }
        
        return $equiposConPuntos;
    }
    
    /**
     * Obtener datos para visualización de bracket
     */
    public function obtenerDatosBracket(): array
    {
        $datos = [
            'tipo_fixture' => $this->tipo_fixture,
            'estado' => $this->estado_torneo,
            'finalizado' => $this->finalizado,
            'enfrentamientos' => []
        ];
        
        // Obtener todos los enfrentamientos con información de equipos
        $enfrentamientos = $this->enfrentamientos()
            ->with(['equipo1', 'equipo2', 'ganador'])
            ->orderBy('fase')
            ->orderBy('ronda')
            ->orderBy('posicion')
            ->get();
        
        foreach ($enfrentamientos as $enfrentamiento) {
            $datos['enfrentamientos'][] = [
                'id' => $enfrentamiento->id,
                'numero_juego' => $enfrentamiento->numero_juego,
                'fase' => $enfrentamiento->fase,
                'ronda' => $enfrentamiento->ronda,
                'posicion' => $enfrentamiento->posicion,
                'grupo' => $enfrentamiento->grupo,
                'equipo1' => $enfrentamiento->equipo1 ? [
                    'id' => $enfrentamiento->equipo1->id,
                    'nombre' => $enfrentamiento->equipo1->nombre,
                ] : null,
                'equipo2' => $enfrentamiento->equipo2 ? [
                    'id' => $enfrentamiento->equipo2->id,
                    'nombre' => $enfrentamiento->equipo2->nombre,
                ] : null,
                'puntaje_equipo1' => $enfrentamiento->puntaje_equipo1,
                'puntaje_equipo2' => $enfrentamiento->puntaje_equipo2,
                'ganador_id' => $enfrentamiento->ganador_id,
                'tiene_resultado' => !is_null($enfrentamiento->ganador_id)
            ];
        }
        
        // Agregar grupos si es formato de grupos
        if (in_array($this->tipo_fixture, [self::TIPO_GRUPOS, self::TIPO_FASE_GRUPOS_ELIMINACION])) {
            $datos['grupos'] = [];
            
            // Obtener todos los grupos
            $grupos = $enfrentamientos->pluck('grupo')->filter()->unique()->values();
            
            foreach ($grupos as $grupo) {
                $datos['grupos'][$grupo] = $this->obtenerResultadosGrupo($grupo);
            }
        }
        
        return $datos;
    }
    
    /**
     * Obtener los resultados finales de la competición
     */
    public function obtenerResultadosFinales(): array
    {
        if (!$this->estaFinalizado()) {
            return [];
        }
        
        $resultados = [];
        
        switch ($this->tipo_fixture) {
            case self::TIPO_ELIMINACION_DIRECTA:
                $resultados = $this->obtenerResultadosEliminacionDirecta();
                break;
                
            case self::TIPO_ELIMINACION_DOBLE:
                $resultados = $this->obtenerResultadosEliminacionDoble();
                break;
                
            case self::TIPO_TODOS_CONTRA_TODOS:
            case self::TIPO_SUIZO:
                $resultados = $this->obtenerResultadosPorPuntos();
                break;
                
            case self::TIPO_GRUPOS:
                $resultados = $this->obtenerResultadosGruposFinales();
                break;
                
            case self::TIPO_FASE_GRUPOS_ELIMINACION:
                $resultados = $this->obtenerResultadosGruposEliminacion();
                break;
        }
        
        return $resultados;
    }
    
    /**
     * Obtener resultados para eliminación directa
     */
    protected function obtenerResultadosEliminacionDirecta(): array
    {
        $resultados = [];
        
        // Obtener la última ronda (final)
        $ultimaRonda = $this->enfrentamientos()
            ->where('fase', self::FASE_FINAL)
            ->orWhere('fase', self::FASE_WINNERS)
            ->max('ronda');
        
        // Obtener el enfrentamiento final
        $final = $this->enfrentamientos()
            ->where(function($query) use ($ultimaRonda) {
                $query->where('fase', self::FASE_FINAL)
                      ->orWhere(function($q) use ($ultimaRonda) {
                          $q->where('fase', self::FASE_WINNERS)
                            ->where('ronda', $ultimaRonda);
                      });
            })
            ->first();
        
        if ($final && $final->ganador_id) {
            // Primer lugar
            $resultados[] = [
                'posicion' => 1,
                'equipo_id' => $final->ganador_id
            ];
            
            // Segundo lugar
            $subcampeonId = null;
            if ($final->equipo1_id == $final->ganador_id) {
                $subcampeonId = $final->equipo2_id;
            } else {
                $subcampeonId = $final->equipo1_id;
            }
            
            if ($subcampeonId) {
                $resultados[] = [
                    'posicion' => 2,
                    'equipo_id' => $subcampeonId
                ];
            }
        }
        
        return $resultados;
    }
    
    /**
     * Obtener resultados para eliminación doble
     */
    protected function obtenerResultadosEliminacionDoble(): array
    {
        $resultados = [];
        
        // Verificar si hubo una segunda final
        $segundaFinal = $this->enfrentamientos()
            ->where('fase', self::FASE_FINAL)
            ->where('ronda', 2)
            ->whereNotNull('ganador_id')
            ->first();
        
        // Si hubo segunda final, el ganador es el campeón
        if ($segundaFinal) {
            $resultados[] = [
                'posicion' => 1,
                'equipo_id' => $segundaFinal->ganador_id
            ];
            
            $subcampeonId = $segundaFinal->ganador_id == $segundaFinal->equipo1_id 
                ? $segundaFinal->equipo2_id 
                : $segundaFinal->equipo1_id;
            
            $resultados[] = [
                'posicion' => 2,
                'equipo_id' => $subcampeonId
            ];
        } else {
            // Si no hubo segunda final, buscar la primera
            $primeraFinal = $this->enfrentamientos()
                ->where('fase', self::FASE_FINAL)
                ->where('ronda', 1)
                ->whereNotNull('ganador_id')
                ->first();
            
            if ($primeraFinal) {
                $resultados[] = [
                    'posicion' => 1,
                    'equipo_id' => $primeraFinal->ganador_id
                ];
                
                $subcampeonId = $primeraFinal->ganador_id == $primeraFinal->equipo1_id 
                    ? $primeraFinal->equipo2_id 
                    : $primeraFinal->equipo1_id;
                
                $resultados[] = [
                    'posicion' => 2,
                    'equipo_id' => $subcampeonId
                ];
            }
        }
        
        // Tercer lugar: último perdedor en el bracket de winners
        $ultimoLoser = $this->enfrentamientos()
            ->where('fase', self::FASE_LOSERS)
            ->orderBy('ronda', 'desc')
            ->first();
        
        if ($ultimoLoser && $ultimoLoser->ganador_id) {
            $tercerLugarId = $ultimoLoser->ganador_id == $ultimoLoser->equipo1_id 
                ? $ultimoLoser->equipo2_id 
                : $ultimoLoser->equipo1_id;
            
            if ($tercerLugarId) {
                $resultados[] = [
                    'posicion' => 3,
                    'equipo_id' => $tercerLugarId
                ];
            }
        }
        
        return $resultados;
    }
    
    /**
     * Obtener resultados por puntos (Round Robin, Swiss)
     */
    protected function obtenerResultadosPorPuntos(): array
    {
        $equiposVictorias = [];
        
        // Obtener todos los enfrentamientos
        $enfrentamientos = $this->enfrentamientos()->get();
        
        // Contar victorias por equipo
        foreach ($enfrentamientos as $enfrentamiento) {
            if ($enfrentamiento->ganador_id) {
                if (!isset($equiposVictorias[$enfrentamiento->ganador_id])) {
                    $equiposVictorias[$enfrentamiento->ganador_id] = 0;
                }
                $equiposVictorias[$enfrentamiento->ganador_id]++;
            }
        }
        
        // Ordenar por número de victorias
        arsort($equiposVictorias);
        
        // Crear array de resultados
        $resultados = [];
        $posicion = 1;
        foreach ($equiposVictorias as $equipoId => $victorias) {
            $resultados[] = [
                'posicion' => $posicion++,
                'equipo_id' => $equipoId,
                'victorias' => $victorias
            ];
        }
        
        return $resultados;
    }
    
    /**
     * Obtener resultados finales de fase de grupos sin eliminación
     */
    protected function obtenerResultadosGruposFinales(): array
    {
        $resultados = [];
        
        // Obtener todos los grupos
        $grupos = $this->enfrentamientos()
            ->where('fase', self::FASE_GRUPOS)
            ->pluck('grupo')
            ->unique()
            ->values();
        
        foreach ($grupos as $grupo) {
            $resultadosGrupo = $this->obtenerResultadosGrupo($grupo);
            
            // Agregar posición dentro del grupo
            foreach ($resultadosGrupo as $index => $resultado) {
                $resultado['grupo'] = $grupo;
                $resultado['posicion_grupo'] = $index + 1;
                $resultados[] = $resultado;
            }
        }
        
        return $resultados;
    }
    
    /**
     * Obtener resultados finales de fase de grupos + eliminación
     */
    protected function obtenerResultadosGruposEliminacion(): array
    {
        // Para fase de grupos + eliminación, los resultados dependen de la fase eliminatoria
        return $this->obtenerResultadosEliminacionDirecta();
    }
    
    /**
     * Avanzar un ganador a la siguiente ronda en el bracket
     * 
     * @param Enfrentamiento $enfrentamiento El enfrentamiento con resultado
     */
    public function avanzarGanador($enfrentamiento): void
    {
        // Si no hay ganador, no avanzamos a nadie
        if (!$enfrentamiento->ganador_id) {
            return;
        }
        
        // Obtener ronda y posición actual
        $rondaActual = $enfrentamiento->ronda;
        $posicionActual = $enfrentamiento->posicion;
        
        // Calcular la siguiente ronda y posición
        $siguienteRonda = $rondaActual + 1;
        $siguientePosicion = ceil($posicionActual / 2);
        
        // Buscar el enfrentamiento de la siguiente ronda donde debería avanzar
        $siguienteEnfrentamiento = $this->enfrentamientos()
            ->where('ronda', $siguienteRonda)
            ->where('posicion', $siguientePosicion)
            ->where('fase', $enfrentamiento->fase)
            ->first();
        
        // Si no hay siguiente enfrentamiento, terminar
        if (!$siguienteEnfrentamiento) {
            return;
        }
        
        // Determinar si el ganador va al slot 1 o 2 del siguiente enfrentamiento
        // Si la posición actual es impar (1,3,5...) va al slot 1, si es par (2,4,6...) va al slot 2
        if ($posicionActual % 2 != 0) {
            // Posición impar, actualizar equipo1
            $siguienteEnfrentamiento->update([
                'equipo1_id' => $enfrentamiento->ganador_id
            ]);
        } else {
            // Posición par, actualizar equipo2
            $siguienteEnfrentamiento->update([
                'equipo2_id' => $enfrentamiento->ganador_id
            ]);
        }
        
        // Refrescar los datos del enfrentamiento después de la actualización
        $siguienteEnfrentamiento->refresh();
        
        // Verificar si ambos equipos están asignados
        if ($siguienteEnfrentamiento->equipo1_id && $siguienteEnfrentamiento->equipo2_id) {
            // Ambos equipos están asignados, no hay bye
            return;
        }
        
        // Verificar si hay un bye (uno de los equipos está vacío)
        if ($siguienteEnfrentamiento->equipo1_id && !$siguienteEnfrentamiento->equipo2_id) {
            // Solo equipo1 presente, avanzar automáticamente
            $siguienteEnfrentamiento->update(['ganador_id' => $siguienteEnfrentamiento->equipo1_id]);
            $this->avanzarGanador($siguienteEnfrentamiento);
        } elseif (!$siguienteEnfrentamiento->equipo1_id && $siguienteEnfrentamiento->equipo2_id) {
            // Solo equipo2 presente, avanzar automáticamente
            $siguienteEnfrentamiento->update(['ganador_id' => $siguienteEnfrentamiento->equipo2_id]);
            $this->avanzarGanador($siguienteEnfrentamiento);
        }
    }
    
    /**
     * Avanzar ganador en doble eliminación
     */
    public function avanzarGanadorDobleEliminacion($enfrentamiento): void
    {
        // Si no hay ganador, no avanzamos a nadie
        if (!$enfrentamiento->ganador_id) {
            return;
        }
        
        // Para winners bracket, avanzar el ganador normalmente
        if ($enfrentamiento->fase === self::FASE_WINNERS) {
            // Avanzar el ganador al siguiente enfrentamiento de winners bracket
            $this->avanzarGanador($enfrentamiento);
            
            // Encontrar el perdedor
            $perdedorId = null;
            if ($enfrentamiento->equipo1_id == $enfrentamiento->ganador_id) {
                $perdedorId = $enfrentamiento->equipo2_id;
            } else {
                $perdedorId = $enfrentamiento->equipo1_id;
            }
            
            // Si no hay perdedor (bye), no lo avanzamos al bracket de losers
            if (!$perdedorId) {
                return;
            }
            
            // Calcular a qué enfrentamiento del losers bracket debe ir el perdedor
            // Esta lógica depende de la estructura específica del double elimination
            $rondaActual = $enfrentamiento->ronda;
            $posicionActual = $enfrentamiento->posicion;
            
            // En la primera ronda de winners, los perdedores van a ronda 1 de losers
            if ($rondaActual == 1) {
                $rondaLosers = 1;
                $posicionLosers = ceil($posicionActual / 2);
                
                // Buscar el enfrentamiento de losers donde debe ir
                $enfrentamientoLosers = $this->enfrentamientos()
                    ->where('fase', self::FASE_LOSERS)
                    ->where('ronda', $rondaLosers)
                    ->where('posicion', $posicionLosers)
                    ->first();
                
                if ($enfrentamientoLosers) {
                    // Colocar al perdedor en el slot correcto
                    if ($posicionActual % 2 != 0) {
                        $enfrentamientoLosers->update(['equipo1_id' => $perdedorId]);
                    } else {
                        $enfrentamientoLosers->update(['equipo2_id' => $perdedorId]);
                    }
                    
                    // Verificar si ambos equipos están asignados en el enfrentamiento de losers
                    $enfrentamientoLosers->refresh();
                    if ($enfrentamientoLosers->equipo1_id && $enfrentamientoLosers->equipo2_id) {
                        // Ambos equipos están presentes, el enfrentamiento es válido
                        return;
                    } else if ($enfrentamientoLosers->equipo1_id && !$enfrentamientoLosers->equipo2_id) {
                        // Solo equipo1 presente, avanzar automáticamente
                        $enfrentamientoLosers->update(['ganador_id' => $enfrentamientoLosers->equipo1_id]);
                        $this->avanzarGanador($enfrentamientoLosers); // Avanzar en losers bracket
                    } else if (!$enfrentamientoLosers->equipo1_id && $enfrentamientoLosers->equipo2_id) {
                        // Solo equipo2 presente, avanzar automáticamente
                        $enfrentamientoLosers->update(['ganador_id' => $enfrentamientoLosers->equipo2_id]);
                        $this->avanzarGanador($enfrentamientoLosers); // Avanzar en losers bracket
                    }
                }
            } 
            // En rondas posteriores, los perdedores van a diferentes rondas de losers
            else {
                // La ronda de losers depende de la ronda de winners
                // Típicamente, perdedores de ronda 2 van a ronda 3 de losers, etc.
                $rondaLosers = $rondaActual * 2 - 1;
                $posicionLosers = ceil($posicionActual);
                
                // Buscar el enfrentamiento de losers donde debe ir
                $enfrentamientoLosers = $this->enfrentamientos()
                    ->where('fase', self::FASE_LOSERS)
                    ->where('ronda', $rondaLosers)
                    ->where('posicion', $posicionLosers)
                    ->first();
                
                if ($enfrentamientoLosers) {
                    // En rondas posteriores, los perdedores suelen ir al slot que esté vacío
                    if (!$enfrentamientoLosers->equipo1_id) {
                        $enfrentamientoLosers->update(['equipo1_id' => $perdedorId]);
                    } else {
                        $enfrentamientoLosers->update(['equipo2_id' => $perdedorId]);
                    }
                    
                    // Verificar si ambos equipos están asignados
                    $enfrentamientoLosers->refresh();
                    if ($enfrentamientoLosers->equipo1_id && $enfrentamientoLosers->equipo2_id) {
                        // Ambos equipos están presentes
                        return;
                    } else if ($enfrentamientoLosers->equipo1_id && !$enfrentamientoLosers->equipo2_id) {
                        // Solo equipo1 presente, avanzar automáticamente
                        $enfrentamientoLosers->update(['ganador_id' => $enfrentamientoLosers->equipo1_id]);
                        $this->avanzarGanador($enfrentamientoLosers);
                    } else if (!$enfrentamientoLosers->equipo1_id && $enfrentamientoLosers->equipo2_id) {
                        // Solo equipo2 presente, avanzar automáticamente
                        $enfrentamientoLosers->update(['ganador_id' => $enfrentamientoLosers->equipo2_id]);
                        $this->avanzarGanador($enfrentamientoLosers);
                    }
                }
            }
        } 
        // Para losers bracket, solo avanzamos al ganador
        else if ($enfrentamiento->fase === self::FASE_LOSERS) {
            $this->avanzarGanador($enfrentamiento);
        }
        // Para fase final (grand final)
        else if ($enfrentamiento->fase === self::FASE_FINAL) {
            // Si es la primera final y gana el equipo de losers, se necesita otra final
            if ($enfrentamiento->ronda == 1) {
                // Determinar si ganó el equipo que venía de losers
                $ganadorVieneDeLosers = false;
                
                // Verificar si el ganador es el equipo que venía de losers
                // Esto depende de cómo estén configurados los enfrentamientos finales
                // Típicamente equipo1 viene de winners y equipo2 de losers
                if ($enfrentamiento->ganador_id == $enfrentamiento->equipo2_id) {
                    $ganadorVieneDeLosers = true;
                }
                
                // Si ganó el equipo de losers, configurar la segunda final
                if ($ganadorVieneDeLosers) {
                    $segundaFinal = $this->enfrentamientos()
                        ->where('fase', self::FASE_FINAL)
                        ->where('ronda', 2)
                        ->first();
                    
                    if ($segundaFinal) {
                        // Asignar los equipos a la segunda final
                        $segundaFinal->update([
                            'equipo1_id' => $enfrentamiento->equipo1_id,
                            'equipo2_id' => $enfrentamiento->equipo2_id
                        ]);
                    }
                } else {
                    // Si ganó el equipo de winners, torneo terminado
                    $this->update([
                        'finalizado' => true,
                        'estado_torneo' => self::ESTADO_FINALIZADO
                    ]);
                }
            } 
            // Si es la segunda final, el torneo termina independientemente de quién gane
            else if ($enfrentamiento->ronda == 2) {
                $this->update([
                    'finalizado' => true,
                    'estado_torneo' => self::ESTADO_FINALIZADO
                ]);
            }
        }
    }
    
    /**
     * Acumular puntos para fase de grupos
     */
    public function acumularPuntosGrupo($enfrentamiento): void
    {
        // Si no estamos en fase de grupos, no hacer nada
        if ($enfrentamiento->fase !== self::FASE_GRUPOS) {
            return;
        }
        
        // Si no hay resultado, no hacer nada
        if (!$enfrentamiento->ganador_id) {
            return;
        }
        
        // Si todos los enfrentamientos del grupo están completos, verificar si se debe avanzar a eliminación
        $grupo = $enfrentamiento->grupo;
        
        $pendientes = $this->enfrentamientos()
            ->where('fase', self::FASE_GRUPOS)
            ->where('grupo', $grupo)
            ->whereNull('ganador_id')
            ->count();
        
        // Si no hay enfrentamientos pendientes en el grupo, y es un torneo con fase eliminatoria
        if ($pendientes == 0 && $this->tipo_fixture == self::TIPO_FASE_GRUPOS_ELIMINACION) {
            // Verificar si todos los grupos están completos
            $todosGruposCompletos = $this->enfrentamientos()
                ->where('fase', self::FASE_GRUPOS)
                ->whereNull('ganador_id')
                ->count() == 0;
            
            if ($todosGruposCompletos) {
                // Configurar enfrentamientos de fase eliminatoria
                $this->configurarFaseEliminatoria();
            }
        }
    }
    
    /**
     * Obtener el número total de equipos en la competición
     */
    public function cantidadEquipos(): int
    {
        return $this->equipos()->count();
    }
    
    /**
     * Verificar si un equipo participa en la competición
     */
    public function tieneEquipo($equipoId): bool
    {
        return $this->equipos()->where('equipo_id', $equipoId)->exists();
    }
    
    /**
     * Obtener estadísticas del torneo
     */
    public function obtenerEstadisticas(): array
    {
        return [
            'total_equipos' => $this->cantidadEquipos(),
            'enfrentamientos_totales' => $this->enfrentamientos()->count(),
            'enfrentamientos_completados' => $this->enfrentamientos()->whereNotNull('ganador_id')->count(),
            'enfrentamientos_pendientes' => $this->enfrentamientos()->whereNull('ganador_id')->count(),
            'rondas_totales' => $this->enfrentamientos()->max('ronda'),
            'ronda_actual' => $this->estructura['ronda_actual'] ?? 1,
            'finalizado' => $this->finalizado
        ];
    }

    public function actualizarEnfrentamientosSiguientes($enfrentamiento)
    {
        // Obtener el enfrentamiento siguiente según la posición
        $enfrentamientoSiguiente = $this->enfrentamientos()
            ->where('ronda', $enfrentamiento->ronda + 1)
            ->where('posicion', ceil($enfrentamiento->posicion / 2))
            ->first();

        if (!$enfrentamientoSiguiente) {
            return;
        }

        // Determinar si el equipo ganador va a la posición 1 o 2 del siguiente enfrentamiento
        $esPosicionImpar = $enfrentamiento->posicion % 2 == 1;
        
        if ($esPosicionImpar) {
            $enfrentamientoSiguiente->update([
                'equipo1_id' => $enfrentamiento->ganador_id
            ]);
        } else {
            $enfrentamientoSiguiente->update([
                'equipo2_id' => $enfrentamiento->ganador_id
            ]);
        }

        // Si es un enfrentamiento de perdedores (double elimination)
        if ($enfrentamiento->es_perdedores) {
            // Obtener el enfrentamiento siguiente en el bracket de perdedores
            $enfrentamientoPerdedoresSiguiente = $this->enfrentamientos()
                ->where('ronda', $enfrentamiento->ronda + 1)
                ->where('es_perdedores', true)
                ->where('posicion', ceil($enfrentamiento->posicion / 2))
                ->first();

            if ($enfrentamientoPerdedoresSiguiente) {
                $esPosicionImpar = $enfrentamiento->posicion % 2 == 1;
                
                if ($esPosicionImpar) {
                    $enfrentamientoPerdedoresSiguiente->update([
                        'equipo1_id' => $enfrentamiento->ganador_id
                    ]);
                } else {
                    $enfrentamientoPerdedoresSiguiente->update([
                        'equipo2_id' => $enfrentamiento->ganador_id
                    ]);
                }
            }
        }
    }
    
    /**
     * Obtener los equipos homologados para esta llave
     */
    public function equiposHomologados()
    {
        return $this->belongsToMany(Equipo::class, 'equipos_llaves')
            ->wherePivot('homologado', true)
            ->withPivot('homologado')
            ->withTimestamps();
    }
    
    /**
     * Verificar si un equipo está homologado para esta llave
     */
    public function equipoHomologado($equipoId): bool
    {
        return $this->equiposHomologados()->where('equipos.id', $equipoId)->exists();
    }
    
    /**
     * Obtener los equipos disponibles para generar el bracket
     */
    public function obtenerEquiposDisponibles(): array
    {
        return $this->equiposHomologados()
            ->whereHas('inscripciones', function($query) {
                $query->where('evento_id', $this->evento_id)
                    ->where('fecha_id', $this->fecha_id)
                    ->where('categoria_evento_id', $this->categoria_evento_id)
                    ->where('estado', 'confirmada');
            })
            ->pluck('equipos.id')
            ->toArray();
    }
} 