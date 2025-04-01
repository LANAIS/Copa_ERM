<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'competition_id',
        'competition_event_id',
        'robot_id',
        'equipo_id',
        'status',
        'notes',
        'approval_date',
        'approved_by',
        'registration_date',
        'evento_id',
        'categoria_evento_id',
        'fecha_evento_id',
        'inscripcion_evento_id',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'approval_date' => 'datetime',
        'registration_date' => 'datetime',
    ];

    /**
     * Obtener el usuario de esta inscripción
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias para mantener compatibilidad
     */
    public function usuario(): BelongsTo
    {
        return $this->user();
    }

    /**
     * Obtener el usuario que aprobó esta inscripción
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Obtener la competición de esta inscripción
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * Obtener el robot de esta inscripción
     */
    public function robot(): BelongsTo
    {
        return $this->belongsTo(Robot::class);
    }

    /**
     * Obtener el equipo de esta inscripción
     */
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    /**
     * Obtener los puntajes de esta inscripción
     */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    /**
     * Obtener el evento de competición de esta inscripción
     */
    public function competitionEvent(): BelongsTo
    {
        return $this->belongsTo(CompetitionEvent::class);
    }

    /**
     * Obtener el evento asociado a esta inscripción
     */
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    /**
     * Obtener la categoría de evento asociada a esta inscripción
     */
    public function categoriaEvento(): BelongsTo
    {
        return $this->belongsTo(CategoriaEvento::class);
    }

    /**
     * Obtener la fecha de evento asociada a esta inscripción
     */
    public function fechaEvento(): BelongsTo
    {
        return $this->belongsTo(FechaEvento::class);
    }

    /**
     * Obtener la inscripción de evento asociada a esta inscripción
     */
    public function inscripcionEvento(): BelongsTo
    {
        return $this->belongsTo(InscripcionEvento::class);
    }
}
