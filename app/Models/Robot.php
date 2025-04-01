<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Robot extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria',
        'imagen',
        'user_id',
        'equipo_id',
        'categoria_id',
    ];

    /**
     * Configuración de casting para atributos
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener el equipo (legacy) al que pertenece el robot
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
    
    /**
     * Obtener el equipo al que pertenece el robot (en español)
     */
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    /**
     * Obtener la categoría a la que pertenece el robot
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Obtener el miembro del equipo que actúa como capitán
     */
    public function miembroCapitan(): BelongsTo
    {
        return $this->belongsTo(MiembroEquipo::class, 'capitan');
    }

    /**
     * Obtener las inscripciones del robot
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
    
    /**
     * Obtener las homologaciones del robot
     */
    public function homologaciones(): HasMany
    {
        return $this->hasMany(Homologacion::class);
    }
    
    /**
     * Obtener la homologación del robot para una categoría de evento específica
     */
    public function obtenerHomologacion($categoriaEventoId)
    {
        return $this->homologaciones()
            ->where('categoria_evento_id', $categoriaEventoId)
            ->first();
    }
    
    /**
     * Verificar si el robot está homologado para una categoría de evento específica
     */
    public function estaHomologado($categoriaEventoId): bool
    {
        $homologacion = $this->obtenerHomologacion($categoriaEventoId);
        return $homologacion && $homologacion->estaAprobada();
    }
    
    /**
     * Scope para filtrar por robots del usuario actual
     */
    public function scopeMine($query)
    {
        return $query->where('user_id', Auth::id());
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
