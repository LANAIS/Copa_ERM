<?php

namespace Database\Seeders;

use App\Models\Evento;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventos = [
            [
                'nombre' => 'Copa Robótica Nacional 2025',
                'descripcion' => 'La competencia más grande de robótica a nivel nacional, con múltiples categorías y premios.',
                'lugar' => 'Centro de Convenciones Metropolitano',
                'fecha_inicio' => Carbon::now()->addMonths(3),
                'fecha_fin' => Carbon::now()->addMonths(3)->addDays(2),
                'inicio_inscripciones' => Carbon::now(),
                'fin_inscripciones' => Carbon::now()->addMonths(2),
                'estado' => 'abierto',
                'publicado' => true,
                'user_id' => 1,
            ],
            [
                'nombre' => 'Hackathon Robótica Educativa',
                'descripcion' => 'Evento enfocado en el desarrollo de soluciones robóticas educativas en 48 horas.',
                'lugar' => 'Universidad Tecnológica Nacional',
                'fecha_inicio' => Carbon::now()->addMonths(1),
                'fecha_fin' => Carbon::now()->addMonths(1)->addDays(2),
                'inicio_inscripciones' => Carbon::now(),
                'fin_inscripciones' => Carbon::now()->addDays(20),
                'estado' => 'abierto',
                'publicado' => true,
                'user_id' => 1,
            ],
            [
                'nombre' => 'Torneo Regional de Sumo Bot',
                'descripcion' => 'Competencia regional especializada en robots de sumo de todas las categorías.',
                'lugar' => 'Polideportivo Municipal',
                'fecha_inicio' => Carbon::now()->addMonths(6),
                'fecha_fin' => Carbon::now()->addMonths(6)->addDays(1),
                'inicio_inscripciones' => Carbon::now()->addMonths(1),
                'fin_inscripciones' => Carbon::now()->addMonths(5),
                'estado' => 'proximamente',
                'publicado' => true,
                'user_id' => 1,
            ],
            [
                'nombre' => 'Desafío de Robótica Inclusiva',
                'descripcion' => 'Evento enfocado en proyectos de robótica para soluciones accesibles e inclusivas.',
                'lugar' => 'Centro Cultural de Ciencias',
                'fecha_inicio' => Carbon::now()->addMonths(2),
                'fecha_fin' => Carbon::now()->addMonths(2)->addDays(1),
                'inicio_inscripciones' => Carbon::now(),
                'fin_inscripciones' => Carbon::now()->addMonths(1)->addDays(15),
                'estado' => 'abierto',
                'publicado' => true,
                'user_id' => 1,
            ],
        ];

        foreach ($eventos as $evento) {
            // Crear slug único
            $evento['slug'] = Str::slug($evento['nombre']) . '-' . uniqid();
            
            Evento::create($evento);
        }
    }
}
