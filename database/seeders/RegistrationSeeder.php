<?php

namespace Database\Seeders;

use App\Models\Registration;
use App\Models\Robot;
use App\Models\CompetitionEvent;
use Illuminate\Database\Seeder;

class RegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los robots
        $robots = Robot::all();
        
        // Obtener todos los eventos de competición activos
        $events = CompetitionEvent::whereDate('registration_end', '>=', now())->get();
        
        if ($events->isEmpty()) {
            $this->command->info('No hay eventos disponibles para inscripción.');
            return;
        }
        
        // Estados posibles para las inscripciones
        $statuses = ['pending', 'approved', 'rejected'];
        $weights = [15, 70, 15]; // Pesos para cada estado (15% pending, 70% approved, 15% rejected)
        
        // Para cada robot, registrarlo en 1-3 eventos al azar
        foreach ($robots as $robot) {
            // Seleccionar eventos aleatorios (entre 1 y 3)
            $numEvents = rand(1, 3);
            $randomEvents = $events->random(min($numEvents, $events->count()));
            
            foreach ($randomEvents as $event) {
                // Asignar un estado aleatorio, con mayor probabilidad de 'approved'
                $rand = rand(1, 100);
                if ($rand <= 15) {
                    $randomStatus = 'pending';
                } elseif ($rand <= 85) {
                    $randomStatus = 'approved';
                } else {
                    $randomStatus = 'rejected';
                }
                
                // Comentarios de verificación según el estado
                $notes = null;
                if ($randomStatus === 'approved') {
                    $notes = 'Verificación completa. Robot cumple con los requisitos.';
                } elseif ($randomStatus === 'rejected') {
                    $notes = 'Robot no cumple con las especificaciones de peso/dimensiones.';
                }
                
                // Crear registro
                Registration::create([
                    'team_id' => $robot->team_id,
                    'robot_id' => $robot->id,
                    'competition_event_id' => $event->id,
                    'status' => $randomStatus,
                    'notes' => $notes,
                    'registration_date' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
} 