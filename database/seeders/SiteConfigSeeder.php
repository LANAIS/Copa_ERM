<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteConfig;

class SiteConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear configuración por defecto si no existe
        if (!SiteConfig::where('key', 'logo')->exists()) {
            SiteConfig::create([
                'key' => 'logo',
                'value' => 'img/logo.png', // Logo predeterminado
            ]);
        }
    }
} 