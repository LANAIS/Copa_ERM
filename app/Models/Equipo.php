<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Equipo extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'nombre',
        'descripcion',
        'sitio_web',
        'email',
        'instagram',
        'facebook',
        'youtube',
        'linkedin',
        'logo',
        'banner',
        'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
    ];
    
    /**
     * Obtener el usuario propietario del equipo
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Obtener los robots asociados al equipo
     */
    public function robots(): HasMany
    {
        return $this->hasMany(Robot::class);
    }
    
    /**
     * Obtener los miembros asociados al equipo
     */
    public function miembros(): HasMany
    {
        return $this->hasMany(MiembroEquipo::class);
    }
    
    /**
     * Obtener las inscripciones a eventos del equipo
     */
    public function inscripcionesEvento(): HasMany
    {
        return $this->hasMany(InscripcionEvento::class);
    }
    
    /**
     * Scope para filtrar por usuario actual
     */
    public function scopeMine($query)
    {
        return $query->where('user_id', Auth::id());
    }
}
