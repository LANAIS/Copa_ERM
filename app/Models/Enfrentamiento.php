<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enfrentamiento extends Model
{
    use HasFactory;
    
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'enfrentamientos';
    
    /**
     * Los atributos que son asignables masivamente.
     */
    protected $fillable = [
        'llave_id',
        'ronda',
        'posicion',
        'equipo1_id',
        'equipo2_id',
        'ganador_id',
        'puntaje_equipo1',
        'puntaje_equipo2',
        'observaciones'
    ];
    
    /**
     * Configuración de casting para atributos
     */
    protected $casts = [
        'ronda' => 'integer',
        'posicion' => 'integer',
        'puntaje_equipo1' => 'integer',
        'puntaje_equipo2' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Obtener la llave asociada
     */
    public function llave(): BelongsTo
    {
        return $this->belongsTo(Llave::class);
    }
    
    /**
     * Obtener el equipo 1
     */
    public function equipo1(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo1_id');
    }
    
    /**
     * Obtener el equipo 2
     */
    public function equipo2(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo2_id');
    }
    
    /**
     * Obtener el equipo ganador
     */
    public function ganador(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'ganador_id');
    }
    
    /**
     * Obtener las puntuaciones asociadas a este enfrentamiento
     */
    public function puntuaciones(): HasMany
    {
        return $this->hasMany(Puntuacion::class);
    }
    
    /**
     * Verificar si el enfrentamiento tiene resultado
     */
    public function tieneResultado(): bool
    {
        return !is_null($this->ganador_id);
    }
    
    /**
     * Verificar si es un enfrentamiento en curso
     */
    public function enCurso(): bool
    {
        return !is_null($this->equipo1_id) && 
               !is_null($this->equipo2_id) && 
               is_null($this->ganador_id);
    }
    
    /**
     * Verificar si es un "bye" (pase automático)
     */
    public function esBye(): bool
    {
        return (is_null($this->equipo1_id) && !is_null($this->equipo2_id)) || 
               (!is_null($this->equipo1_id) && is_null($this->equipo2_id));
    }
    
    /**
     * Registrar resultado
     */
    public function registrarResultado(int $puntajeEquipo1, int $puntajeEquipo2, ?int $ganadorId = null): void
    {
        // Determinar ganador automáticamente si no se especificó
        if (is_null($ganadorId)) {
            if ($puntajeEquipo1 > $puntajeEquipo2) {
                $ganadorId = $this->equipo1_id;
            } elseif ($puntajeEquipo2 > $puntajeEquipo1) {
                $ganadorId = $this->equipo2_id;
            }
        }
        
        $this->update([
            'puntaje_equipo1' => $puntajeEquipo1,
            'puntaje_equipo2' => $puntajeEquipo2,
            'ganador_id' => $ganadorId
        ]);
    }
    
    /**
     * Scope para filtrar por ronda
     */
    public function scopePorRonda($query, $ronda)
    {
        return $query->where('ronda', $ronda);
    }
    
    /**
     * Scope para filtrar enfrentamientos sin resultados
     */
    public function scopeSinResultado($query)
    {
        return $query->whereNull('ganador_id');
    }
    
    /**
     * Scope para filtrar enfrentamientos con resultados
     */
    public function scopeConResultado($query)
    {
        return $query->whereNotNull('ganador_id');
    }
    
    /**
     * Accessor para obtener una descripción del enfrentamiento
     */
    public function getDescripcionAttribute(): string
    {
        $categoria = $this->llave->categoriaEvento->categoria->nombre ?? 'Sin categoría';
        $ronda = "Ronda {$this->ronda}";
        $equipo1 = $this->equipo1->nombre ?? 'TBD';
        $equipo2 = $this->equipo2->nombre ?? 'TBD';
        
        return "{$categoria} - {$ronda} - {$equipo1} vs {$equipo2}";
    }
} 