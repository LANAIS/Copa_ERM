<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'year',
        'description',
        'active',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
        'year' => 'integer',
    ];

    /**
     * Obtener los eventos de esta competición
     */
    public function events(): HasMany
    {
        return $this->hasMany(CompetitionEvent::class);
    }
}
