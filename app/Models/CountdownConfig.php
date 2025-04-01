<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountdownConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value'
    ];

    /**
     * Obtener el valor de la fecha objetivo del countdown
     */
    public static function getTargetDate()
    {
        $config = self::where('key', 'target_date')->first();
        return $config ? $config->value : now()->addDays(75)->format('Y-m-d H:i:s');
    }

    /**
     * Establecer la fecha objetivo del countdown
     */
    public static function setTargetDate($date)
    {
        return self::updateOrCreate(
            ['key' => 'target_date'],
            ['value' => $date]
        );
    }
} 