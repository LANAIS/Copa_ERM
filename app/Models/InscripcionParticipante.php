<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InscripcionParticipante extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'categoria_id',
        'nombre_equipo',
        'nombre_institucion',
        'nombre_robot',
        'descripcion_proyecto',
        'miembros_equipo',
        'telefono_contacto',
        'estado',
        'notas_participante',
        'notas_admin',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Obtener el usuario al que pertenece esta inscripción
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Obtener la categoría a la que pertenece esta inscripción
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
    
    /**
     * Scopes para filtrar por estado
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }
    
    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobada');
    }
    
    public function scopeRechazadas($query)
    {
        return $query->where('estado', 'rechazada');
    }
    
    /**
     * Scope para filtrar por usuario actual
     */
    public function scopeMine($query)
    {
        return $query->where('user_id', auth()->id());
    }
}
