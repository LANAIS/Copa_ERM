<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\CompetitionEvent;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CompetitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear la competencia principal
        $competition = Competition::create([
            'name' => 'Copa Robótica Misiones 2025',
            'description' => 'La competencia más importante de robótica de Misiones, con múltiples categorías y eventos.',
            'year' => 2025,
            'active' => true,
        ]);

        // Obtener todas las categorías
        $categories = Category::all();

        // Definir las fechas de los eventos
        $eventDates = [
            // Evento 1: Sumo y Lucha
            [
                'date' => Carbon::create(2025, 4, 15),
                'categories' => ['sumo', 'lucha'],
                'location' => 'Centro de Convenciones Metropolitano',
                'description' => 'Primera fecha: Competencias de Sumo y Lucha'
            ],
            // Evento 2: Seguidor de Línea
            [
                'date' => Carbon::create(2025, 4, 22),
                'categories' => ['seguidor'],
                'location' => 'Instituto Nacional de Robótica',
                'description' => 'Segunda fecha: Competencias de Seguidor de Línea'
            ],
            // Evento 3: Laberinto
            [
                'date' => Carbon::create(2025, 4, 29),
                'categories' => ['laberinto'],
                'location' => 'Parque Tecnológico Industrial',
                'description' => 'Tercera fecha: Competencias de Laberinto'
            ],
            // Evento 4: Finales
            [
                'date' => Carbon::create(2025, 5, 6),
                'categories' => ['sumo', 'lucha', 'seguidor', 'laberinto'],
                'location' => 'Centro de Convenciones Metropolitano',
                'description' => 'Finales: Todas las categorías'
            ]
        ];

        // Crear eventos para cada fecha
        foreach ($eventDates as $eventData) {
            $eventDate = $eventData['date'];
            $location = $eventData['location'];
            $description = $eventData['description'];

            // Filtrar categorías por tipo
            $eventCategories = $categories->filter(function ($category) use ($eventData) {
                return in_array($category->type, $eventData['categories']);
            });

            // Crear eventos para cada categoría
            foreach ($eventCategories as $category) {
                // Fecha de cierre de inscripción (1 semana antes del evento)
                $registrationEnd = (clone $eventDate)->subWeek();

                // Horarios del evento
                $startTime = Carbon::createFromTime(9, 0, 0);
                $endTime = Carbon::createFromTime(18, 0, 0);

                CompetitionEvent::create([
                    'competition_id' => $competition->id,
                    'category_id' => $category->id,
                    'location' => $location,
                    'event_date' => $eventDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'registration_start' => now(),
                    'registration_end' => $registrationEnd,
                    'completed' => false,
                ]);
            }
        }
    }
} 