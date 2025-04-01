<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class InscripcionEvento extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'evento_id',
        'categoria_evento_id',
        'equipo_id',
        'user_id',
        'robot_id',
        'estado',
        'notas_participante',
        'notas_admin',
        'codigo_confirmacion',
        'monto_pagado',
        'comprobante_pago',
        'fecha_confirmacion',
        'fecha_pago',
        'robots_participantes',
        'email',
        'registration_id',
    ];
    
    protected $casts = [
        'monto_pagado' => 'decimal:2',
        'fecha_confirmacion' => 'datetime',
        'fecha_pago' => 'datetime',
        'robots_participantes' => 'array',
    ];
    
    /**
     * Estados de la inscripción
     */
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_CONFIRMADA = 'confirmada';
    const ESTADO_PAGADA = 'pagada';
    const ESTADO_RECHAZADA = 'rechazada';
    const ESTADO_CANCELADA = 'cancelada';
    const ESTADO_HOMOLOGADA = 'homologada';
    const ESTADO_PARTICIPANDO = 'participando';
    const ESTADO_FINALIZADA = 'finalizada';
    
    /**
     * Obtener el evento relacionado
     */
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }
    
    /**
     * Obtener la categoría del evento
     */
    public function categoriaEvento(): BelongsTo
    {
        return $this->belongsTo(CategoriaEvento::class);
    }
    
    /**
     * Obtener el equipo inscrito
     */
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }
    
    /**
     * Obtener el usuario que realizó la inscripción
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Obtener el robot inscrito (si aplica)
     */
    public function robot(): BelongsTo
    {
        return $this->belongsTo(Robot::class);
    }
    
    /**
     * Obtener los robots participantes de la inscripción
     */
    public function getRobotsParticipantes()
    {
        if (empty($this->robots_participantes)) {
            return collect();
        }
        
        // Convertir los IDs a objetos Robot
        $robotIds = collect($this->robots_participantes)->pluck('id')->toArray();
        return Robot::whereIn('id', $robotIds)->get()->map(function ($robot) {
            $data = collect($this->robots_participantes)->firstWhere('id', $robot->id);
            $robot->homologado = $data['homologado'] ?? false;
            $robot->participante = $data['participante'] ?? false;
            return $robot;
        });
    }
    
    /**
     * Acceso directo a los robots participantes como una relación
     */
    public function robotsParticipantes()
    {
        $robotIds = empty($this->robots_participantes) 
            ? [] 
            : collect($this->robots_participantes)
                ->where('participante', true)
                ->pluck('id')
                ->toArray();
        
        return Robot::whereIn('id', $robotIds);
    }
    
    /**
     * Agregar o actualizar un robot participante
     */
    public function actualizarRobotParticipante($robotId, $participante = true, $homologado = false)
    {
        $robotsParticipantes = $this->robots_participantes ?: [];
        
        // Buscar si el robot ya está en el array
        $existe = false;
        foreach ($robotsParticipantes as $key => $robotData) {
            if ($robotData['id'] == $robotId) {
                $robotsParticipantes[$key]['participante'] = $participante;
                $robotsParticipantes[$key]['homologado'] = $homologado;
                $existe = true;
                break;
            }
        }
        
        // Si no existe, agregarlo
        if (!$existe) {
            $robotsParticipantes[] = [
                'id' => $robotId,
                'participante' => $participante,
                'homologado' => $homologado
            ];
        }
        
        $this->robots_participantes = $robotsParticipantes;
        $this->save();
        
        return $this;
    }
    
    /**
     * Inicializar automáticamente todos los robots del equipo como participantes
     */
    public function inicializarRobotsEquipo()
    {
        // Obtener la categoría para filtrar los robots por modalidad
        $categoriaEvento = $this->categoriaEvento;
        if (!$categoriaEvento || !$categoriaEvento->categoria) {
            return $this;
        }
        
        // Obtener todos los robots del equipo que coincidan con la modalidad de la categoría
        $robots = Robot::where('equipo_id', $this->equipo_id)
            ->where('modalidad', $categoriaEvento->categoria->nombre)
            ->get();
        
        // Inicializar el array de robots participantes
        $robotsParticipantes = [];
        
        foreach ($robots as $robot) {
            $robotsParticipantes[] = [
                'id' => $robot->id,
                'participante' => true, // Marcamos como participantes a todos los robots que coinciden con la modalidad
                'homologado' => false
            ];
        }
        
        $this->robots_participantes = $robotsParticipantes;
        $this->save();
        
        return $this;
    }
    
    /**
     * Actualizar estado de homologación de todos los robots
     */
    public function actualizarEstadoHomologacion(): void
    {
        if (empty($this->robots_participantes)) {
            return;
        }
        
        $robotsParticipantes = $this->robots_participantes;
        $actualizados = false;
        
        foreach ($robotsParticipantes as $key => $robotData) {
            if (!$robotData['participante']) {
                continue;
            }
            
            $robot = Robot::find($robotData['id']);
            if (!$robot) {
                continue;
            }
            
            // Verificar si el robot está homologado para esta categoría
            $homologado = $robot->estaHomologado($this->categoria_evento_id);
            
            // Actualizar el estado de homologación si es diferente
            if ($robotData['homologado'] !== $homologado) {
                $robotsParticipantes[$key]['homologado'] = $homologado;
                $actualizados = true;
            }
        }
        
        // Si hubo actualizaciones, guardar los cambios
        if ($actualizados) {
            $this->robots_participantes = $robotsParticipantes;
            $this->save();
        }
    }
    
    /**
     * Verificar si todos los robots están homologados
     */
    public function todosRobotsHomologados(): bool
    {
        if (empty($this->robots_participantes)) {
            return false;
        }
        
        $robotsParticipantes = collect($this->robots_participantes)->where('participante', true);
        
        // Si no hay robots participantes, retornar false
        if ($robotsParticipantes->isEmpty()) {
            return false;
        }
        
        // Verificar que todos estén homologados
        return $robotsParticipantes->every(function ($robot) {
            return $robot['homologado'];
        });
    }
    
    /**
     * Homologar un robot participante
     */
    public function homologarRobot($robotId)
    {
        // Primero verificamos si la homologación existe y está aprobada
        $robot = Robot::find($robotId);
        if (!$robot) {
            return $this;
        }
        
        $homologado = $robot->estaHomologado($this->categoria_evento_id);
        
        return $this->actualizarRobotParticipante($robotId, true, $homologado);
    }
    
    /**
     * Actualizar estado según la fase de la competencia
     */
    public function actualizarEstadoSegunCompetencia(): void
    {
        $categoriaEvento = $this->categoriaEvento;
        
        if (!$categoriaEvento) {
            return;
        }
        
        // Si estamos en fase de homologación y todos los robots están homologados
        if ($categoriaEvento->enHomologacion() && $this->todosRobotsHomologados()) {
            $this->marcarHomologada();
        }
        // Si estamos en fase de competencia
        elseif ($categoriaEvento->enCurso()) {
            $this->update(['estado' => self::ESTADO_PARTICIPANDO]);
        }
        // Si la competencia finalizó
        elseif ($categoriaEvento->finalizada()) {
            $this->update(['estado' => self::ESTADO_FINALIZADA]);
        }
    }
    
    /**
     * Confirmar la inscripción
     */
    public function confirmar(): void
    {
        $this->update([
            'estado' => self::ESTADO_CONFIRMADA,
            'fecha_confirmacion' => now(),
        ]);
        
        $this->categoriaEvento->incrementarInscritos();
    }
    
    /**
     * Rechazar la inscripción
     */
    public function rechazar(): void
    {
        $this->update([
            'estado' => self::ESTADO_RECHAZADA,
        ]);
    }
    
    /**
     * Cancelar la inscripción
     */
    public function cancelar(): void
    {
        if ($this->estado == self::ESTADO_CONFIRMADA) {
            $this->categoriaEvento->decrementarInscritos();
        }
        
        $this->update([
            'estado' => self::ESTADO_CANCELADA,
        ]);
    }
    
    /**
     * Registrar pago
     */
    public function registrarPago(float $monto, string $comprobante = null): void
    {
        $this->update([
            'estado' => self::ESTADO_PAGADA,
            'monto_pagado' => $monto,
            'comprobante_pago' => $comprobante,
            'fecha_pago' => now(),
        ]);
    }
    
    /**
     * Scope para filtrar inscripciones pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }
    
    /**
     * Scope para filtrar inscripciones confirmadas
     */
    public function scopeConfirmadas($query)
    {
        return $query->where('estado', self::ESTADO_CONFIRMADA);
    }
    
    /**
     * Scope para filtrar inscripciones pagadas
     */
    public function scopePagadas($query)
    {
        return $query->where('estado', self::ESTADO_PAGADA);
    }
    
    /**
     * Scope para filtrar inscripciones homologadas
     */
    public function scopeHomologadas($query)
    {
        return $query->where('estado', self::ESTADO_HOMOLOGADA);
    }
    
    /**
     * Scope para filtrar inscripciones participando
     */
    public function scopeParticipando($query)
    {
        return $query->where('estado', self::ESTADO_PARTICIPANDO);
    }
    
    /**
     * Scope para filtrar inscripciones finalizadas
     */
    public function scopeFinalizadas($query)
    {
        return $query->where('estado', self::ESTADO_FINALIZADA);
    }
    
    /**
     * Scope para filtrar inscripciones del usuario actual
     */
    public function scopeMias($query)
    {
        return $query->where('user_id', Auth::id() ?? 0);
    }
    
    /**
     * Obtener el registro de inscripción relacionado (modelo antiguo)
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
    
    /**
     * Marcar la inscripción como homologada
     */
    public function marcarHomologada(): void
    {
        $this->fill(['estado' => self::ESTADO_HOMOLOGADA]);
        $this->save();
    }
}
