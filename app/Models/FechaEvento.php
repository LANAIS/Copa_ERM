<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FechaEvento extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'evento_id',
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'lugar',
        'orden',
        'activo',
    ];
    
    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'activo' => 'boolean',
    ];
    
    /**
     * Obtener el evento al que pertenece esta fecha
     */
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }
    
    /**
     * Obtener las categorías asociadas a esta fecha de evento
     */
    public function categorias(): HasMany
    {
        return $this->hasMany(CategoriaEvento::class);
    }
    
    /**
     * Scope para filtrar fechas activas
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
    
    /**
     * Scope para filtrar fechas futuras
     */
    public function scopeFuturas($query)
    {
        return $query->where('fecha_inicio', '>', now());
    }
    
    /**
     * Scope para ordernar por orden
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden', 'asc')->orderBy('fecha_inicio', 'asc');
    }
}
