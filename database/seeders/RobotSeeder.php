<?php

namespace Database\Seeders;

use App\Models\Robot;
use App\Models\Team;
use App\Models\Category;
use Illuminate\Database\Seeder;

class RobotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los equipos
        $teams = Team::all();
        
        // Nombres de robots
        $robotNames = [
            'Destructor', 'Megabyte', 'Thunderbolt', 'Striker', 'Blaster',
            'Terminator', 'Velocity', 'Genesis', 'Quantum', 'Pulse',
            'Titan', 'Phoenix', 'Cyclone', 'Vulcan', 'Neptune',
            'Skynet', 'Nemesis', 'Neutron', 'Dynamo', 'Phantom'
        ];

        // Categorías disponibles
        $categories = Category::all();
        
        // Crear 2-3 robots por equipo
        foreach ($teams as $team) {
            $numRobots = rand(2, 3);
            
            for ($i = 0; $i < $numRobots; $i++) {
                // Seleccionar un nombre aleatorio
                $robotName = $robotNames[array_rand($robotNames)];
                
                // Añadir un número al final del nombre para evitar duplicados
                $uniqueRobotName = $robotName . ' ' . chr(65 + $i);
                
                // Seleccionar una categoría aleatoria
                $category = $categories->random();
                
                Robot::create([
                    'name' => $uniqueRobotName,
                    'team_id' => $team->id,
                    'model' => 'Modelo ' . rand(1, 10),
                    'description' => $this->getRandomSpecifications($category)
                ]);
            }
        }
    }

    /**
     * Generar especificaciones aleatorias según la categoría
     */
    private function getRandomSpecifications(Category $category): string
    {
        $specs = [];
        
        // Especificaciones comunes
        $specs[] = 'Peso: ' . rand(1, 10) . '.' . rand(0, 9) . ' kg';
        $specs[] = 'Dimensiones: ' . rand(20, 60) . 'x' . rand(20, 60) . 'x' . rand(10, 30) . ' cm';
        $specs[] = 'Batería: ' . rand(7, 24) . 'V ' . rand(1000, 5000) . 'mAh';
        
        // Especificaciones específicas según la categoría
        if (str_contains($category->name, 'Fútbol')) {
            $specs[] = 'Motores: ' . rand(2, 4) . ' x DC Brushless';
            $specs[] = 'Control: RC ' . rand(2, 6) . ' canales';
            $specs[] = 'Velocidad máxima: ' . rand(1, 5) . '.' . rand(0, 9) . ' m/s';
        } elseif (str_contains($category->name, 'Carrera')) {
            $specs[] = 'Motores: ' . rand(2, 4) . ' x DC ' . rand(100, 300) . 'rpm';
            $specs[] = 'Tracción: ' . (rand(0, 1) ? '4x4' : '4x2');
            $specs[] = 'Velocidad máxima: ' . rand(2, 10) . '.' . rand(0, 9) . ' m/s';
        } elseif (str_contains($category->name, 'Sumo')) {
            $specs[] = 'Motores: ' . rand(2, 4) . ' x DC de alto torque';
            $specs[] = 'Sensores: ' . rand(2, 8) . ' sensores IR';
            $specs[] = 'Microcontrolador: ' . (rand(0, 1) ? 'Arduino' : 'Raspberry Pi');
        } elseif (str_contains($category->name, 'Laberinto')) {
            $specs[] = 'Motores: ' . rand(2, 4) . ' x Servo';
            $specs[] = 'Sensores: ' . rand(3, 6) . ' sensores ultrasónicos';
            $specs[] = 'Algoritmo: ' . (rand(0, 1) ? 'Seguimiento de pared' : 'Dijkstra');
        } else {
            $specs[] = 'Controlador: ' . (rand(0, 1) ? 'PIC' : 'ARM');
            $specs[] = 'Comunicación: ' . (rand(0, 1) ? 'Bluetooth' : 'WiFi');
            $specs[] = 'Programación: ' . (rand(0, 1) ? 'C++' : 'Python');
        }
        
        return implode(', ', $specs);
    }
} 