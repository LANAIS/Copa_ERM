<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'description',
        'rules',
    ];

    /**
     * Obtener los eventos de competición que usan esta categoría
     */
    public function competitionEvents(): HasMany
    {
        return $this->hasMany(CompetitionEvent::class);
    }
}
