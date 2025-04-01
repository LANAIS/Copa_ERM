<?php

namespace Database\Seeders;

use App\Models\Score;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;

class ScoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener inscripciones aprobadas
        $registrations = Registration::where('status', 'approved')->get();
        
        if ($registrations->isEmpty()) {
            $this->command->info('No hay inscripciones aprobadas para asignar puntajes.');
            return;
        }
        
        // Obtener usuarios administradores que pueden asignar puntuaciones
        $admins = User::where('role', 'admin')->get();
        
        if ($admins->isEmpty()) {
            $this->command->info('No hay administradores para asignar puntajes.');
            return;
        }
        
        // Asignar puntajes a aproximadamente el 60% de las inscripciones aprobadas (simulando competencias completadas)
        $scoredRegistrations = $registrations->random(intval($registrations->count() * 0.6));
        
        foreach ($scoredRegistrations as $registration) {
            // Generar puntajes aleatorios
            $points = rand(0, 100);
            
            // Generar observaciones según el puntaje
            $comments = $this->getObservations($points);
            
            // Asignar a un administrador aleatorio
            $assignedBy = $admins->random()->id;
            
            // Crear registro de puntaje
            Score::create([
                'registration_id' => $registration->id,
                'points' => $points,
                'comments' => $comments,
                'assigned_by' => $assignedBy,
            ]);
        }
    }
    
    /**
     * Generar observaciones según la puntuación obtenida
     */
    private function getObservations(int $points): string
    {
        if ($points >= 90) {
            return "Excelente desempeño en todos los aspectos. Robot destacó por su precisión y velocidad.";
        } elseif ($points >= 75) {
            return "Muy buen rendimiento. Algunas dificultades menores en la prueba de precisión.";
        } elseif ($points >= 50) {
            return "Desempeño aceptable. Necesita mejorar en respuesta a obstáculos y velocidad.";
        } elseif ($points >= 25) {
            return "Rendimiento por debajo del promedio. Múltiples errores durante la competencia.";
        } else {
            return "Rendimiento deficiente. El robot tuvo problemas significativos para completar la prueba.";
        }
    }
} 