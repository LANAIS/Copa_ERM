<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Evento extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'lugar',
        'banner',
        'slug',
        'fecha_inicio',
        'fecha_fin',
        'inicio_inscripciones',
        'fin_inscripciones',
        'estado',
        'publicado',
        'user_id',
    ];
    
    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'inicio_inscripciones' => 'datetime',
        'fin_inscripciones' => 'datetime',
        'publicado' => 'boolean',
    ];
    
    protected static function booted()
    {
        static::creating(function ($evento) {
            if (empty($evento->slug)) {
                $evento->slug = Str::slug($evento->nombre) . '-' . uniqid();
            }
        });
    }
    
    /**
     * Obtener las fechas asociadas al evento
     */
    public function fechas(): HasMany
    {
        return $this->hasMany(FechaEvento::class);
    }
    
    /**
     * Obtener las categorías asociadas al evento
     */
    public function categorias(): HasMany
    {
        return $this->hasMany(CategoriaEvento::class);
    }
    
    /**
     * Obtener las inscripciones al evento
     */
    public function inscripciones(): HasMany
    {
        return $this->hasMany(InscripcionEvento::class);
    }
    
    /**
     * Obtener el creador del evento
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Verificar si las inscripciones están abiertas
     */
    public function inscripcionesAbiertas(): bool
    {
        $ahora = now();
        return $this->estado == 'abierto' && 
               $ahora >= $this->inicio_inscripciones && 
               $ahora <= $this->fin_inscripciones;
    }
    
    /**
     * Scope para filtrar eventos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('publicado', true)
                    ->where('estado', '!=', 'finalizado');
    }
    
    /**
     * Scope para filtrar eventos con inscripciones abiertas
     */
    public function scopeConInscripcionesAbiertas($query)
    {
        $ahora = now();
        return $query->where('publicado', true)
                    ->where('estado', 'abierto')
                    ->where('inicio_inscripciones', '<=', $ahora)
                    ->where('fin_inscripciones', '>=', $ahora);
    }
    
    /**
     * Scope para filtrar eventos próximos
     */
    public function scopeProximos($query)
    {
        $ahora = now();
        return $query->where('publicado', true)
                    ->where('fecha_inicio', '>', $ahora);
    }
}
