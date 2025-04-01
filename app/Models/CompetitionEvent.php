<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionEvent extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'competition_id',
        'category_id',
        'location',
        'event_date',
        'start_time',
        'end_time',
        'registration_start',
        'registration_end',
        'completed',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'event_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'registration_start' => 'date',
        'registration_end' => 'date',
        'completed' => 'boolean',
    ];

    /**
     * Obtener la competición a la que pertenece este evento
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * Obtener la categoría de este evento
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Obtener las inscripciones para este evento
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
