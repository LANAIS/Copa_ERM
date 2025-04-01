<?php

namespace Database\Seeders;

use App\Models\EventDate;
use Illuminate\Database\Seeder;

class EventDateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Eliminar datos existentes
        EventDate::truncate();

        // Crear fechas de eventos
        $eventDates = [
            [
                'name' => 'Fecha 1',
                'location' => 'Comandante Andresito',
                'date' => '2025-09-13',
                'categories' => ['Sumo', 'Seguidor de Línea', 'Desafío Creativo'],
                'is_final' => false,
                'order' => 1,
                'active' => true,
            ],
            [
                'name' => 'Fecha 2',
                'location' => 'Posadas',
                'date' => '2025-10-11',
                'categories' => ['Sumo', 'Seguidor de Línea', 'Programación'],
                'is_final' => false,
                'order' => 2,
                'active' => true,
            ],
            [
                'name' => 'Fecha 3',
                'location' => 'Eldorado',
                'date' => '2025-11-15',
                'categories' => ['Sumo', 'Seguidor de Línea', 'Innovación'],
                'is_final' => false,
                'order' => 3,
                'active' => true,
            ],
            [
                'name' => 'Fecha 4',
                'location' => 'Oberá',
                'date' => '2025-12-13',
                'categories' => ['Todas las categorías'],
                'is_final' => true,
                'order' => 4,
                'active' => true,
            ],
        ];

        foreach ($eventDates as $eventDate) {
            EventDate::create($eventDate);
        }
    }
} 