<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Fútbol RC',
                'descripcion' => 'Competencia de fútbol con robots controlados remotamente',
                'icono' => 'fas fa-futbol',
                'activo' => true,
                'orden' => 1
            ],
            [
                'nombre' => 'Carrera',
                'descripcion' => 'Competencia de velocidad en pista con obstáculos',
                'icono' => 'fas fa-flag-checkered',
                'activo' => true,
                'orden' => 2
            ],
            [
                'nombre' => 'Sumo Autónomo',
                'descripcion' => 'Robots autónomos que compiten en formato sumo',
                'icono' => 'fas fa-robot',
                'activo' => true,
                'orden' => 3
            ],
            [
                'nombre' => 'MiniSumo',
                'descripcion' => 'Competencia con robots de menor tamaño en formato sumo',
                'icono' => 'fas fa-microchip',
                'activo' => true,
                'orden' => 4
            ],
            [
                'nombre' => 'Laberinto',
                'descripcion' => 'Robots que deben resolver un laberinto en el menor tiempo posible',
                'icono' => 'fas fa-route',
                'activo' => true,
                'orden' => 5
            ],
            [
                'nombre' => 'Innovación',
                'descripcion' => 'Proyectos creativos que usan robótica para solucionar problemas reales',
                'icono' => 'fas fa-lightbulb',
                'activo' => true,
                'orden' => 6
            ],
            [
                'nombre' => 'Seguidor de Línea',
                'descripcion' => 'Robots que siguen una línea en un circuito predeterminado',
                'icono' => 'fas fa-road',
                'activo' => true,
                'orden' => 7
            ],
            [
                'nombre' => 'Programación',
                'descripcion' => 'Desafíos de programación con robots educativos',
                'icono' => 'fas fa-code',
                'activo' => true,
                'orden' => 8
            ],
            [
                'nombre' => 'Desafío Creativo',
                'descripcion' => 'Creación libre con temática específica',
                'icono' => 'fas fa-brain',
                'activo' => true,
                'orden' => 9
            ]
        ];

        foreach ($categorias as $categoria) {
            Categoria::updateOrCreate(
                ['nombre' => $categoria['nombre']],
                $categoria
            );
        }
    }
}
