<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Puntuacion extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo
     */
    protected $table = 'puntuaciones';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'enfrentamiento_id',
        'equipo_id',
        'puntos',
        'penalizacion',
        'total',
        'notas',
        'juez_id',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'puntos' => 'float',
        'penalizacion' => 'float',
        'total' => 'float',
    ];

    /**
     * Obtener el enfrentamiento al que pertenece esta puntuación
     */
    public function enfrentamiento(): BelongsTo
    {
        return $this->belongsTo(Enfrentamiento::class);
    }

    /**
     * Obtener el equipo al que pertenece esta puntuación
     */
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    /**
     * Obtener el juez que registró esta puntuación
     */
    public function juez(): BelongsTo
    {
        return $this->belongsTo(User::class, 'juez_id');
    }

    /**
     * Calcular el total de puntos (puntos - penalización)
     */
    public function calcularTotal(): float
    {
        return $this->puntos - $this->penalizacion;
    }

    /**
     * Guardar y actualizar el total
     */
    public function actualizarTotal(): void
    {
        $this->total = $this->calcularTotal();
        $this->save();
    }
} 