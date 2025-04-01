<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InscripcionEvento;
use App\Models\Registration;

class CheckInscripcion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:inscripcion {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica los datos de inscripciones existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');
        
        if ($id) {
            // Buscar inscripción específica
            $inscripcion = InscripcionEvento::find($id);
            
            if (!$inscripcion) {
                $this->error("Inscripción con ID {$id} no encontrada.");
                return 1;
            }
            
            $this->showInscripcion($inscripcion);
        } else {
            // Buscar todas las inscripciones
            $inscripciones = InscripcionEvento::orderBy('id', 'desc')->limit(5)->get();
            
            if ($inscripciones->isEmpty()) {
                $this->error("No hay inscripciones disponibles.");
                return 1;
            }
            
            $this->info("Últimas 5 inscripciones:");
            
            foreach ($inscripciones as $inscripcion) {
                $this->showInscripcion($inscripcion);
                $this->line('------------------------');
            }
        }
        
        return 0;
    }
    
    private function showInscripcion(InscripcionEvento $inscripcion)
    {
        $this->info("Inscripción ID: {$inscripcion->id}");
        $this->line("Evento ID: {$inscripcion->evento_id}");
        $this->line("Categoría Evento ID: {$inscripcion->categoria_evento_id}");
        $this->line("Fecha Evento ID: " . ($inscripcion->fecha_evento_id ?? 'NULL'));
        $this->line("Equipo ID: {$inscripcion->equipo_id}");
        $this->line("Usuario ID: {$inscripcion->user_id}");
        $this->line("Robot ID: {$inscripcion->robot_id}");
        $this->line("Estado: {$inscripcion->estado}");
        
        // Verificar robots_participantes
        if (empty($inscripcion->robots_participantes)) {
            $this->line("Robots participantes: VACÍO");
        } else {
            $this->line("Robots participantes: " . json_encode($inscripcion->robots_participantes));
        }
        
        // Verificar la registration asociada
        if ($inscripcion->registration_id) {
            $registration = Registration::find($inscripcion->registration_id);
            
            if ($registration) {
                $this->info("Registration asociada ID: {$registration->id}");
                $this->line("Registration robot_id: {$registration->robot_id}");
                $this->line("Registration fecha: " . ($registration->registration_date ? $registration->registration_date->format('Y-m-d H:i:s') : 'NULL'));
            } else {
                $this->warn("¡Registration asociada no encontrada! ID: {$inscripcion->registration_id}");
            }
        } else {
            $this->warn("No hay Registration asociada");
        }
    }
}
