<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Homologacion extends Model
{
    use HasFactory;
    
    /**
     * Constantes para estados de homologación
     */
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_APROBADO = 'aprobado';
    const ESTADO_RECHAZADO = 'rechazado';
    
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'homologaciones';
    
    /**
     * Atributos asignables masivamente
     */
    protected $fillable = [
        'robot_id',
        'categoria_evento_id',
        'juez_id',
        'estado',
        'resultado',
        'peso',
        'ancho',
        'largo',
        'alto',
        'dimensiones',
        'observaciones'
    ];
    
    /**
     * Configuración de casting de atributos
     */
    protected $casts = [
        'dimensiones' => 'array',
        'peso' => 'float',
        'ancho' => 'float',
        'largo' => 'float',
        'alto' => 'float'
    ];
    
    /**
     * Relación con Robot
     */
    public function robot(): BelongsTo
    {
        return $this->belongsTo(Robot::class);
    }
    
    /**
     * Relación con CategoriaEvento
     */
    public function categoriaEvento(): BelongsTo
    {
        return $this->belongsTo(CategoriaEvento::class);
    }
    
    /**
     * Relación con el evaluador (User)
     */
    public function evaluador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'juez_id');
    }
    
    /**
     * Verificar si la homologación fue aprobada
     */
    public function estaAprobada(): bool
    {
        return $this->estado === self::ESTADO_APROBADO;
    }
    
    /**
     * Verificar si la homologación fue rechazada
     */
    public function estaRechazada(): bool
    {
        return $this->estado === self::ESTADO_RECHAZADO;
    }

    /**
     * Verificar si la homologación está pendiente
     */
    public function estaPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    /**
     * Método para crear datos dummy para pruebas
     */
    public static function crearDummy()
    {
        return [
            'robot_id' => 1,
            'categoria_evento_id' => 1,
            'peso' => 0.5,
            'ancho' => 20,
            'largo' => 30,
            'alto' => 15,
            'estado' => self::ESTADO_PENDIENTE,
            'observaciones' => 'Pendiente de revisar'
        ];
    }
}
