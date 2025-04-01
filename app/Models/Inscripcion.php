<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscripcion extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nombre',
        'email',
        'categoria_id',
        'institucion',
        'telefono',
        'notas',
        'estado',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
    ];
    
    /**
     * Obtener la categoría a la que pertenece la inscripción.
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
}
