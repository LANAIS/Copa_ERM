<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fecha extends Model
{
    use HasFactory;
    
    // Especificar el nombre correcto de la tabla
    protected $table = 'fecha_eventos';
    
    protected $fillable = [
        'evento_id',
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'lugar',
        'orden',
        'activo'
    ];
    
    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'activo' => 'boolean'
    ];
    
    /**
     * Obtener el evento asociado
     */
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }
    
    /**
     * Obtener las llaves asociadas
     */
    public function llaves(): HasMany
    {
        return $this->hasMany(Llave::class);
    }
    
    /**
     * Obtener las inscripciones asociadas
     */
    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }
    
    /**
     * Verificar si la fecha está activa
     */
    public function estaActiva(): bool
    {
        return $this->activo === true;
    }
    
    /**
     * Verificar si la fecha está finalizada
     */
    public function estaFinalizada(): bool
    {
        return $this->activo === false;
    }
    
    /**
     * Verificar si la fecha está pendiente
     */
    public function estaPendiente(): bool
    {
        return $this->activo === null;
    }
} 