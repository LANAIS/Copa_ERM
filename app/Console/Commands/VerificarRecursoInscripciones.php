<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class VerificarRecursoInscripciones extends Command
{
    protected $signature = 'app:verificar-recurso-inscripciones';
    protected $description = 'Verifica si el recurso de inscripciones está correctamente configurado';

    public function handle()
    {
        $this->info('Verificando el recurso de inscripciones...');
        
        // Comprobar si el archivo existe
        $path = app_path('Filament\Resources\InscripcionesEventoResource.php');
        if (File::exists($path)) {
            $this->info('✅ El archivo del recurso existe: ' . $path);
        } else {
            $this->error('❌ El archivo del recurso NO existe: ' . $path);
            return 1;
        }
        
        // Comprobar si las páginas existen
        $pagesPath = app_path('Filament\Resources\InscripcionesEventoResource\Pages');
        if (File::exists($pagesPath)) {
            $this->info('✅ El directorio de páginas existe: ' . $pagesPath);
            
            // Verificar páginas específicas
            $listPage = $pagesPath . '\ListInscripcionesEventos.php';
            $viewPage = $pagesPath . '\ViewInscripcionEvento.php';
            
            if (File::exists($listPage)) {
                $this->info('✅ La página de listado existe: ' . $listPage);
            } else {
                $this->error('❌ La página de listado NO existe: ' . $listPage);
            }
            
            if (File::exists($viewPage)) {
                $this->info('✅ La página de vista existe: ' . $viewPage);
            } else {
                $this->error('❌ La página de vista NO existe: ' . $viewPage);
            }
        } else {
            $this->error('❌ El directorio de páginas NO existe: ' . $pagesPath);
            return 1;
        }
        
        // Comprobar contenido del archivo
        $content = File::get($path);
        
        if (strpos($content, 'protected static ?string $navigationGroup = \'Competencia\';') !== false) {
            $this->info('✅ El recurso está configurado para el grupo de navegación "Competencia"');
        } else {
            $this->error('❌ El recurso NO está configurado para el grupo de navegación "Competencia"');
        }
        
        if (strpos($content, 'protected static ?string $slug = \'inscripciones\';') !== false) {
            $this->info('✅ El recurso tiene configurada la ruta personalizada "/inscripciones"');
        } else {
            $this->error('❌ El recurso NO tiene configurada la ruta personalizada "/inscripciones"');
        }
        
        $this->info('Verificación completa.');
        return 0;
    }
} 