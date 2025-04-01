<?php

namespace Database\Seeders;

use App\Models\Robot;
use App\Models\Equipo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class RobotsNuevosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los equipos
        $equipos = Equipo::all();
        
        if ($equipos->isEmpty()) {
            $this->command->info('No hay equipos disponibles. Por favor, crea algunos equipos primero.');
            return;
        }
        
        // Nombres de robots
        $robotNames = [
            'Destructor', 'Megabyte', 'Thunderbolt', 'Striker', 'Blaster',
            'Terminator', 'Velocity', 'Genesis', 'Quantum', 'Pulse',
            'Titan', 'Phoenix', 'Cyclone', 'Vulcan', 'Neptune',
            'Skynet', 'Nemesis', 'Neutron', 'Dynamo', 'Phantom'
        ];

        // Zonas disponibles
        $zonas = ['Zona 1', 'Zona 2', 'Zona 3', 'Zona 4', 'Zona 5'];
        
        // Modalidades disponibles
        $modalidades = [
            'Sumo', 'Seguidor de Línea', 'Carrera', 'Fútbol RC',
            'Laberinto', 'Innovación', 'Desafío Creativo', 'Programación'
        ];
        
        // Capitanes
        $capitanes = ['Capitán 1', 'Capitán 2', 'Capitán 3'];
        
        // Verificar que exista la carpeta para guardar imágenes
        $storagePath = storage_path('app/public/robots/fotos');
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }
        
        // Copiar una imagen de ejemplo si no existe
        $sampleImagePath = public_path('img/robot_sample.jpg');
        $destinationPath = storage_path('app/public/robots/fotos/robot_sample.jpg');
        
        if (!File::exists($sampleImagePath)) {
            // Crear una imagen en blanco como ejemplo
            $image = imagecreatetruecolor(300, 300);
            $bgColor = imagecolorallocate($image, 255, 255, 255);
            $textColor = imagecolorallocate($image, 0, 0, 0);
            
            imagefill($image, 0, 0, $bgColor);
            imagestring($image, 5, 50, 150, 'Robot Sample Image', $textColor);
            
            imagejpeg($image, $sampleImagePath);
            imagedestroy($image);
        }
        
        if (File::exists($sampleImagePath) && !File::exists($destinationPath)) {
            File::copy($sampleImagePath, $destinationPath);
        }
        
        // Crear 1-2 robots por equipo
        foreach ($equipos as $equipo) {
            $numRobots = rand(1, 2);
            
            for ($i = 0; $i < $numRobots; $i++) {
                // Seleccionar un nombre aleatorio
                $robotName = $robotNames[array_rand($robotNames)];
                
                // Añadir un número al final del nombre para evitar duplicados
                $uniqueRobotName = $robotName . ' ' . chr(65 + $i);
                
                Robot::create([
                    'nombre' => $uniqueRobotName,
                    'equipo_id' => $equipo->id,
                    'zona' => $zonas[array_rand($zonas)],
                    'modalidad' => $modalidades[array_rand($modalidades)],
                    'capitan' => $capitanes[array_rand($capitanes)],
                    'foto' => 'robots/fotos/robot_sample.jpg'
                ]);
            }
        }
        
        $this->command->info('Robots creados exitosamente.');
    }
} 