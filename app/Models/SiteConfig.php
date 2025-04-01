<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SiteConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value'
    ];

    /**
     * Obtener el valor de la configuración por llave
     */
    public static function getValue($key, $default = null)
    {
        try {
            // Verificar si la tabla existe antes de consultar
            if (!Schema::hasTable('site_configs')) {
                return $default;
            }
            
            $config = self::where('key', $key)->first();
            return $config ? $config->value : $default;
        } catch (\Exception $e) {
            // En caso de error, devolver el valor por defecto
            return $default;
        }
    }

    /**
     * Establecer valor de configuración
     */
    public static function setValue($key, $value)
    {
        try {
            // Verificar si la tabla existe antes de modificar
            if (!Schema::hasTable('site_configs')) {
                return null;
            }
            
            return self::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtener la URL del logo
     */
    public static function getLogo()
    {
        // Comprobar si estamos en proceso de instalación
        if (file_exists(storage_path('app/installing')) || !Schema::hasTable('site_configs')) {
            return asset('img/logo.png');
        }
        
        try {
            $logo = self::getValue('site_logo');
            return $logo ? asset('storage/' . $logo) : asset('img/logo.png');
        } catch (\Exception $e) {
            return asset('img/logo.png');
        }
    }

    /**
     * Establecer logo
     */
    public static function setLogo($path)
    {
        try {
            // Verificar si la tabla existe antes de modificar
            if (!Schema::hasTable('site_configs')) {
                return null;
            }
            
            return self::setValue('site_logo', $path);
        } catch (\Exception $e) {
            return null;
        }
    }
} 