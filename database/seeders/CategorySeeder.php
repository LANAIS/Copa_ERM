<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Categorías de Sumo
            [
                'name' => 'Sumo Autónomo',
                'type' => 'sumo',
                'description' => 'Robots autónomos que compiten en un ring de sumo',
                'rules' => json_encode([
                    'peso_maximo' => '1kg',
                    'dimensiones_maximas' => '20x20x20cm',
                    'tiempo_combate' => '3 minutos',
                    'criterios_victoria' => [
                        'expulsar al oponente del ring',
                        'hacer caer al oponente',
                        'puntos por técnica'
                    ]
                ])
            ],
            [
                'name' => 'Sumo RC',
                'type' => 'sumo',
                'description' => 'Robots controlados por radio que compiten en un ring de sumo',
                'rules' => json_encode([
                    'peso_maximo' => '1kg',
                    'dimensiones_maximas' => '20x20x20cm',
                    'tiempo_combate' => '3 minutos',
                    'criterios_victoria' => [
                        'expulsar al oponente del ring',
                        'hacer caer al oponente',
                        'puntos por técnica'
                    ]
                ])
            ],

            // Categorías de Seguidor de Línea
            [
                'name' => 'Seguidor de Línea Velocista',
                'type' => 'seguidor',
                'description' => 'Robots que siguen una línea negra en el menor tiempo posible',
                'rules' => json_encode([
                    'peso_maximo' => '2kg',
                    'dimensiones_maximas' => '30x30x30cm',
                    'tiempo_maximo' => '3 minutos',
                    'criterios_victoria' => [
                        'completar el circuito en el menor tiempo',
                        'no salirse de la línea',
                        'no tocar los obstáculos'
                    ]
                ])
            ],
            [
                'name' => 'Seguidor de Línea Obstáculos',
                'type' => 'seguidor',
                'description' => 'Robots que siguen una línea negra y evitan obstáculos',
                'rules' => json_encode([
                    'peso_maximo' => '2kg',
                    'dimensiones_maximas' => '30x30x30cm',
                    'tiempo_maximo' => '3 minutos',
                    'criterios_victoria' => [
                        'completar el circuito en el menor tiempo',
                        'evitar obstáculos',
                        'no salirse de la línea'
                    ]
                ])
            ],

            // Categorías de Laberinto
            [
                'name' => 'Laberinto Velocista',
                'type' => 'laberinto',
                'description' => 'Robots que encuentran la salida del laberinto en el menor tiempo',
                'rules' => json_encode([
                    'peso_maximo' => '2kg',
                    'dimensiones_maximas' => '30x30x30cm',
                    'tiempo_maximo' => '5 minutos',
                    'criterios_victoria' => [
                        'encontrar la salida en el menor tiempo',
                        'no tocar las paredes',
                        'no usar sensores externos'
                    ]
                ])
            ],
            [
                'name' => 'Laberinto Velocista RC',
                'type' => 'laberinto',
                'description' => 'Robots controlados por radio que encuentran la salida del laberinto',
                'rules' => json_encode([
                    'peso_maximo' => '2kg',
                    'dimensiones_maximas' => '30x30x30cm',
                    'tiempo_maximo' => '5 minutos',
                    'criterios_victoria' => [
                        'encontrar la salida en el menor tiempo',
                        'no tocar las paredes',
                        'control manual permitido'
                    ]
                ])
            ],

            // Categorías de Lucha
            [
                'name' => 'Lucha RC',
                'type' => 'lucha',
                'description' => 'Robots controlados por radio que compiten en combate',
                'rules' => json_encode([
                    'peso_maximo' => '3kg',
                    'dimensiones_maximas' => '40x40x40cm',
                    'tiempo_combate' => '3 minutos',
                    'criterios_victoria' => [
                        'inmovilizar al oponente',
                        'hacer caer al oponente',
                        'puntos por técnica'
                    ]
                ])
            ],
            [
                'name' => 'Lucha Autónoma',
                'type' => 'lucha',
                'description' => 'Robots autónomos que compiten en combate',
                'rules' => json_encode([
                    'peso_maximo' => '3kg',
                    'dimensiones_maximas' => '40x40x40cm',
                    'tiempo_combate' => '3 minutos',
                    'criterios_victoria' => [
                        'inmovilizar al oponente',
                        'hacer caer al oponente',
                        'puntos por técnica'
                    ]
                ])
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
