<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\CategoriaEvento;
use App\Models\Evento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriaEventoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los eventos
        $eventos = Evento::all();
        
        // Obtener todas las categorías
        $categorias = Categoria::all();
        
        foreach ($eventos as $evento) {
            // Asignar algunas categorías aleatoriamente a cada evento
            $categoriasAleatorias = $categorias->random(rand(2, $categorias->count()));
            
            foreach ($categoriasAleatorias as $categoria) {
                $inscripcionesAbiertas = $evento->estado === 'abierto' && rand(0, 1) === 1;
                $precioBase = rand(5, 50) * 10; // Precio base entre $50 y $500
                
                CategoriaEvento::create([
                    'evento_id' => $evento->id,
                    'categoria_id' => $categoria->id,
                    'reglas_especificas' => "Reglas específicas para {$categoria->nombre} en el evento {$evento->nombre}. Los participantes deberán cumplir con todas las normas generales más las específicas de esta categoría.",
                    'requisitos' => "- Robot con un tamaño máximo permitido\n- Controlado de forma autónoma o remota\n- Equipo de 2 a 4 participantes\n- Todos los materiales deben ser suministrados por el equipo",
                    'participantes_min' => rand(1, 3),
                    'participantes_max' => rand(4, 8),
                    'cupo_limite' => rand(0, 1) ? rand(10, 30) : null,
                    'inscritos' => 0,
                    'precio_inscripcion' => $precioBase,
                    'activo' => true,
                    'inscripciones_abiertas' => $inscripcionesAbiertas,
                ]);
            }
        }
    }
}
