<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class MiembroEquipo extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'equipo_id',
        'user_id',
        'nombre',
        'email',
        'telefono',
        'rol',
        'es_capitan',
        'activo'
    ];
    
    protected $casts = [
        'es_capitan' => 'boolean',
        'activo' => 'boolean',
    ];
    
    protected static function booted()
    {
        static::creating(function ($miembro) {
            if (!$miembro->nombre && $miembro->user_id) {
                $user = User::find($miembro->user_id);
                if ($user) {
                    $miembro->nombre = $user->name;
                    $miembro->email = $miembro->email ?: $user->email;
                }
            }
        });

        static::updating(function ($miembro) {
            if ($miembro->isDirty('user_id') && $miembro->user_id) {
                $user = User::find($miembro->user_id);
                if ($user) {
                    $miembro->nombre = $user->name;
                    $miembro->email = $user->email;
                }
            }
        });
    }
    
    /**
     * Obtener el equipo al que pertenece este miembro
     */
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }
    
    /**
     * Obtener el usuario asociado a este miembro (si existe)
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Scope para filtrar capitanes
     */
    public function scopeCapitanes($query)
    {
        return $query->where('es_capitan', true);
    }
    
    /**
     * Scope para filtrar miembros activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
