<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CategoriaEvento extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'evento_id',
        'categoria_id',
        'fecha_evento_id',
        'reglas_especificas',
        'requisitos',
        'participantes_min',
        'participantes_max',
        'cupo_limite',
        'inscritos',
        'precio_inscripcion',
        'activo',
        'inscripciones_abiertas',
        'estado_competencia',
        'tipo_fixture',
    ];
    
    protected $casts = [
        'participantes_min' => 'integer',
        'participantes_max' => 'integer',
        'cupo_limite' => 'integer',
        'inscritos' => 'integer',
        'precio_inscripcion' => 'decimal:2',
        'activo' => 'boolean',
        'inscripciones_abiertas' => 'boolean',
    ];
    
    /**
     * Estados disponibles para la competencia
     */
    const ESTADO_CREADA = 'creada';
    const ESTADO_INSCRIPCIONES = 'inscripciones';
    const ESTADO_HOMOLOGACION = 'homologacion';
    const ESTADO_ARMADO_LLAVES = 'armado_llaves';
    const ESTADO_EN_CURSO = 'en_curso';
    const ESTADO_FINALIZADA = 'finalizada';
    
    /**
     * Tipos de fixture para la competencia
     */
    const FIXTURE_TODOS_CONTRA_TODOS = 'todos_contra_todos';
    const FIXTURE_ELIMINACION_DIRECTA = 'eliminacion_directa';
    const FIXTURE_SUIZO = 'suizo';
    
    /**
     * Obtener el evento al que pertenece esta categoría
     */
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }
    
    /**
     * Obtener la categoría base
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
    
    /**
     * Obtener las inscripciones para esta categoría en este evento
     */
    public function inscripciones(): HasMany
    {
        return $this->hasMany(InscripcionEvento::class);
    }
    
    /**
     * Obtener las homologaciones relacionadas con esta categoría
     */
    public function homologaciones(): HasMany
    {
        return $this->hasMany(Homologacion::class);
    }
    
    /**
     * Obtener la llave (fixture) para esta categoría
     */
    public function llave(): HasOne
    {
        return $this->hasOne(Llave::class);
    }
    
    /**
     * Obtener la fecha del evento a la que pertenece esta categoría
     */
    public function fecha_evento(): BelongsTo
    {
        return $this->belongsTo(FechaEvento::class);
    }
    
    /**
     * Verificar si hay cupo disponible
     */
    public function hayCupo(): bool
    {
        if (!$this->cupo_limite) {
            return true;
        }
        
        return $this->inscritos < $this->cupo_limite;
    }
    
    /**
     * Verificar si las inscripciones están abiertas
     */
    public function inscripcionesAbiertas(): bool
    {
        return $this->activo && 
               $this->inscripciones_abiertas && 
               $this->evento->inscripcionesAbiertas() &&
               $this->hayCupo();
    }
    
    /**
     * Verificar si la categoría está en fase de inscripciones
     */
    public function enInscripciones(): bool
    {
        return $this->estado_competencia === self::ESTADO_INSCRIPCIONES;
    }
    
    /**
     * Verificar si la categoría está en fase de homologación
     */
    public function enHomologacion(): bool
    {
        return $this->estado_competencia === self::ESTADO_HOMOLOGACION;
    }
    
    /**
     * Verificar si la categoría está en fase de armado de llaves
     */
    public function enArmadoLlaves(): bool
    {
        return $this->estado_competencia === self::ESTADO_ARMADO_LLAVES;
    }
    
    /**
     * Verificar si la categoría está en curso
     */
    public function enCurso(): bool
    {
        return $this->estado_competencia === self::ESTADO_EN_CURSO;
    }
    
    /**
     * Verificar si la categoría está finalizada
     */
    public function finalizada(): bool
    {
        return $this->estado_competencia === self::ESTADO_FINALIZADA;
    }
    
    /**
     * Cambiar el estado de la competencia a inscripciones
     */
    public function abrirInscripciones(): void
    {
        $this->update([
            'estado_competencia' => self::ESTADO_INSCRIPCIONES,
            'inscripciones_abiertas' => true
        ]);
    }
    
    /**
     * Cerrar inscripciones
     */
    public function cerrarInscripciones(): void
    {
        $this->update(['inscripciones_abiertas' => false]);
    }
    
    /**
     * Cambiar el estado de la competencia a homologación
     */
    public function iniciarHomologacion(): void
    {
        // Cerramos inscripciones
        $this->cerrarInscripciones();
        
        // Crear entradas de homologación para cada robot inscrito
        $this->crearHomologaciones();
        
        // Actualizamos el estado
        $this->update(['estado_competencia' => self::ESTADO_HOMOLOGACION]);
    }
    
    /**
     * Cambiar el estado de la competencia a armado de llaves
     */
    public function iniciarArmadoLlaves(): void
    {
        $this->update(['estado_competencia' => self::ESTADO_ARMADO_LLAVES]);
    }
    
    /**
     * Cambiar el estado de la competencia a en curso
     */
    public function iniciarCompetencia(): void
    {
        $this->update(['estado_competencia' => self::ESTADO_EN_CURSO]);
    }
    
    /**
     * Cambiar el estado de la competencia a finalizada
     */
    public function finalizarCompetencia(): void
    {
        $this->update(['estado_competencia' => self::ESTADO_FINALIZADA]);
    }
    
    /**
     * Crear homologaciones para todos los robots inscritos
     */
    public function crearHomologaciones(): void
    {
        // Eliminar homologaciones existentes
        $this->homologaciones()->delete();
        
        // Obtener todos los robots de los equipos inscritos
        $robotsIds = $this->inscripciones()
            ->where('estado', 'aprobada')
            ->with('robotsParticipantes')
            ->get()
            ->pluck('robotsParticipantes')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->toArray();
        
        // Crear una homologación para cada robot
        foreach ($robotsIds as $robotId) {
            $this->homologaciones()->create([
                'robot_id' => $robotId,
                'estado' => 'pendiente',
                'resultado' => null,
                'peso' => null,
                'ancho' => null,
                'largo' => null,
                'alto' => null
            ]);
        }
    }
    
    /**
     * Verificar si todas las homologaciones están completas
     */
    public function homologacionesCompletas(): bool
    {
        // Verificar si todas las homologaciones están procesadas (aprobadas o rechazadas)
        return $this->homologaciones()
            ->where('estado', Homologacion::ESTADO_PENDIENTE)
            ->doesntExist();
    }
    
    /**
     * Crear llave según el tipo de fixture configurado
     */
    public function crearLlave(): void
    {
        // Eliminar llave existente si hay
        if ($this->llave) {
            $this->llave->delete();
        }
        
        // Obtener equipos inscritos cuyos robots fueron homologados correctamente
        $equiposIds = $this->obtenerEquiposConRobotsHomologados();
        
        // Crear nueva llave
        $llave = $this->llave()->create([
            'tipo_fixture' => $this->tipo_fixture ?: Llave::TIPO_ELIMINACION_DIRECTA,
            'finalizado' => false
        ]);
        
        // Generar fixture según el tipo
        switch ($llave->tipo_fixture) {
            case Llave::TIPO_TODOS_CONTRA_TODOS:
                $llave->generarTodosContraTodos($equiposIds);
                break;
                
            case Llave::TIPO_SUIZO:
                $llave->generarSuizo($equiposIds, 5); // 5 rondas por defecto
                break;
                
            case Llave::TIPO_ELIMINACION_DIRECTA:
            default:
                $llave->generarEliminacionDirecta($equiposIds);
                break;
        }
    }
    
    /**
     * Obtener los equipos que tienen todos sus robots homologados
     */
    public function obtenerEquiposConRobotsHomologados(): array
    {
        $equiposIds = [];
        
        // Obtener las inscripciones
        $inscripciones = $this->inscripciones()->with('robotsParticipantes')->get();
        
        foreach ($inscripciones as $inscripcion) {
            // Verificar si todos los robots del equipo están homologados
            $robotsIds = $inscripcion->robotsParticipantes->pluck('id')->toArray();
            
            $robotsHomologados = $this->homologaciones()
                ->whereIn('robot_id', $robotsIds)
                ->where('estado', Homologacion::ESTADO_APROBADO)
                ->count();
            
            // Si todos los robots están homologados, agregar el equipo
            if ($robotsHomologados === count($robotsIds) && $robotsHomologados > 0) {
                $equiposIds[] = $inscripcion->equipo_id;
            }
        }
        
        return $equiposIds;
    }
    
    /**
     * Obtener los resultados finales de la competición
     */
    public function obtenerResultadosFinales(): array
    {
        if (!$this->llave || !$this->finalizada()) {
            return [];
        }
        
        $resultados = [];
        
        switch ($this->llave->tipo_fixture) {
            case Llave::TIPO_ELIMINACION_DIRECTA:
                // Para eliminación directa, buscar los últimos enfrentamientos
                $ultimaRonda = $this->llave->enfrentamientos()->max('ronda');
                
                // El ganador del enfrentamiento de la última ronda es el campeón
                $finalEnfrentamiento = $this->llave->enfrentamientos()
                    ->where('ronda', $ultimaRonda)
                    ->first();
                
                if ($finalEnfrentamiento && $finalEnfrentamiento->ganador_id) {
                    $resultados[] = [
                        'posicion' => 1,
                        'equipo_id' => $finalEnfrentamiento->ganador_id
                    ];
                    
                    // El otro equipo es el subcampeón
                    $subcampeonId = ($finalEnfrentamiento->ganador_id === $finalEnfrentamiento->equipo1_id) 
                        ? $finalEnfrentamiento->equipo2_id 
                        : $finalEnfrentamiento->equipo1_id;
                    
                    if ($subcampeonId) {
                        $resultados[] = [
                            'posicion' => 2,
                            'equipo_id' => $subcampeonId
                        ];
                    }
                }
                break;
                
            case Llave::TIPO_TODOS_CONTRA_TODOS:
            case Llave::TIPO_SUIZO:
            default:
                // Para todos contra todos y suizo, contar victorias
                $equiposVictorias = [];
                
                // Obtener todos los enfrentamientos
                $enfrentamientos = $this->llave->enfrentamientos()->with(['equipo1', 'equipo2', 'ganador'])->get();
                
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
                $posicion = 1;
                foreach ($equiposVictorias as $equipoId => $victorias) {
                    $resultados[] = [
                        'posicion' => $posicion++,
                        'equipo_id' => $equipoId,
                        'victorias' => $victorias
                    ];
                }
                break;
        }
        
        return $resultados;
    }
    
    /**
     * Incrementar el contador de inscritos
     */
    public function incrementarInscritos(): void
    {
        $this->increment('inscritos');
    }
    
    /**
     * Decrementar el contador de inscritos
     */
    public function decrementarInscritos(): void
    {
        if ($this->inscritos > 0) {
            $this->decrement('inscritos');
        }
    }
    
    /**
     * Scope para filtrar categorías activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
    
    /**
     * Scope para filtrar categorías con inscripciones abiertas
     */
    public function scopeConInscripcionesAbiertas($query)
    {
        return $query->where('activo', true)
                    ->where('inscripciones_abiertas', true)
                    ->whereHas('evento', function ($q) {
                        $q->conInscripcionesAbiertas();
                    });
    }
    
    /**
     * Scope para filtrar categorías por estado de competencia
     */
    public function scopePorEstadoCompetencia($query, $estado)
    {
        return $query->where('estado_competencia', $estado);
    }
    
    /**
     * Scope para filtrar categorías en inscripciones
     */
    public function scopeEnInscripciones($query)
    {
        return $query->where('estado_competencia', self::ESTADO_INSCRIPCIONES);
    }
    
    /**
     * Scope para filtrar categorías en homologación
     */
    public function scopeEnHomologacion($query)
    {
        return $query->where('estado_competencia', self::ESTADO_HOMOLOGACION);
    }
    
    /**
     * Scope para filtrar categorías en armado de llaves
     */
    public function scopeEnArmadoLlaves($query)
    {
        return $query->where('estado_competencia', self::ESTADO_ARMADO_LLAVES);
    }
    
    /**
     * Scope para filtrar categorías en curso
     */
    public function scopeEnCurso($query)
    {
        return $query->where('estado_competencia', self::ESTADO_EN_CURSO);
    }
    
    /**
     * Scope para filtrar categorías finalizadas
     */
    public function scopeFinalizadas($query)
    {
        return $query->where('estado_competencia', self::ESTADO_FINALIZADA);
    }
}
