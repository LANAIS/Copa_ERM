<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'institution',
        'city',
        'description',
        'user_id',
    ];

    /**
     * Obtener el usuario propietario del equipo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener los robots del equipo
     */
    public function robots(): HasMany
    {
        return $this->hasMany(Robot::class);
    }

    /**
     * Obtener las inscripciones del equipo
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
