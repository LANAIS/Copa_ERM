<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'date',
        'categories',
        'is_final',
        'order',
        'active',
    ];

    protected $casts = [
        'date' => 'date',
        'categories' => 'array',
        'is_final' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Obtener las fechas activas ordenadas por orden y fecha
     */
    public static function getActive()
    {
        return self::where('active', true)
            ->orderBy('order')
            ->orderBy('date')
            ->get();
    }

    /**
     * Formatear la fecha para mostrar
     */
    public function getFormattedDateAttribute()
    {
        return $this->date->format('d/m/Y');
    }

    /**
     * Obtener el nombre de visualización para la fecha
     */
    public function getDisplayNameAttribute()
    {
        return $this->is_final ? 'Fecha Final' : $this->name;
    }
} 