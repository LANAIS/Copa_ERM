<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'icono',
        'activo',
        'orden',
        'modalidad',
        'tipo',
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];
    
    /**
     * Obtener las inscripciones para esta categoría.
     */
    public function inscripciones(): HasMany
    {
        return $this->hasMany(InscripcionParticipante::class);
    }
}
