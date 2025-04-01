<?php

namespace Database\Seeders;

use App\Models\Evento;
use App\Models\FechaEvento;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FechaEventoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los eventos
        $eventos = Evento::all();
        
        foreach ($eventos as $evento) {
            // Crear varias fechas para cada evento
            $fechaBase = $evento->fecha_inicio->copy();
            
            // Primera fecha (inauguración)
            FechaEvento::create([
                'evento_id' => $evento->id,
                'nombre' => 'Inauguración y registro',
                'descripcion' => 'Ceremonia de apertura y registro de participantes',
                'fecha_inicio' => $fechaBase->copy()->setHour(9)->setMinute(0),
                'fecha_fin' => $fechaBase->copy()->setHour(12)->setMinute(0),
                'lugar' => $evento->lugar . ' - Salón Principal',
                'orden' => 1,
                'activo' => true,
            ]);
            
            // Segunda fecha (competencias preliminares)
            FechaEvento::create([
                'evento_id' => $evento->id,
                'nombre' => 'Ronda de competencias preliminares',
                'descripcion' => 'Primeras rondas de competencia en todas las categorías',
                'fecha_inicio' => $fechaBase->copy()->setHour(14)->setMinute(0),
                'fecha_fin' => $fechaBase->copy()->setHour(19)->setMinute(0),
                'lugar' => $evento->lugar . ' - Área de Competencia',
                'orden' => 2,
                'activo' => true,
            ]);
            
            // Tercera fecha (semifinales y finales - día siguiente)
            FechaEvento::create([
                'evento_id' => $evento->id,
                'nombre' => 'Semifinales y finales',
                'descripcion' => 'Rondas finales y premiación de ganadores',
                'fecha_inicio' => $fechaBase->copy()->addDay()->setHour(10)->setMinute(0),
                'fecha_fin' => $fechaBase->copy()->addDay()->setHour(18)->setMinute(0),
                'lugar' => $evento->lugar . ' - Área de Competencia',
                'orden' => 3,
                'activo' => true,
            ]);
            
            // Si el evento dura más de 2 días, agregar una fecha más
            if ($evento->fecha_fin->diffInDays($evento->fecha_inicio) > 1) {
                FechaEvento::create([
                    'evento_id' => $evento->id,
                    'nombre' => 'Exposición de proyectos',
                    'descripcion' => 'Exhibición de proyectos de robótica y clausura del evento',
                    'fecha_inicio' => $fechaBase->copy()->addDays(2)->setHour(10)->setMinute(0),
                    'fecha_fin' => $fechaBase->copy()->addDays(2)->setHour(16)->setMinute(0),
                    'lugar' => $evento->lugar . ' - Área de Exposiciones',
                    'orden' => 4,
                    'activo' => true,
                ]);
            }
        }
    }
}
