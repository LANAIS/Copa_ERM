<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios no administradores
        $users = User::where('role', 'user')->get();
        
        // Nombres de equipos
        $teamNames = [
            'RoboWarriors',
            'TechTitans',
            'MechaMinds',
            'SiliconSoldiers',
            'CircuitBreakers',
            'NanoNinjas',
            'QuantumQuest',
            'ByteBots',
            'ElectroEngineers',
            'CyberCrusaders',
            'LaserLords',
            'CodeCrafters',
            'DigitalDynamos',
            'RoboRavens',
            'WiredWizards'
        ];
        
        // Instituciones educativas
        $institutions = [
            'Universidad Tecnológica',
            'Instituto Politécnico',
            'Colegio San Ignacio',
            'Escuela Técnica Superior',
            'Academia de Ciencias',
            'Centro de Estudios Avanzados',
            'Instituto Nacional',
            'Colegio Experimental',
            'Universidad Latinoamericana',
            'Escuela de Robótica'
        ];

        // Ciudades
        $cities = [
            'Ciudad de México',
            'Guadalajara',
            'Monterrey',
            'Puebla',
            'Querétaro',
            'Tijuana',
            'Mérida',
            'Cancún',
            'Veracruz',
            'Morelia'
        ];
        
        // Crear 1-2 equipos por usuario
        foreach ($users as $user) {
            $numTeams = rand(1, 2);
            
            for ($i = 0; $i < $numTeams; $i++) {
                $teamName = $teamNames[array_rand($teamNames)];
                $institution = $institutions[array_rand($institutions)];
                $city = $cities[array_rand($cities)];
                
                // Añadir un número al final del nombre para evitar duplicados
                $uniqueTeamName = $teamName . ' ' . chr(65 + $i);
                
                Team::create([
                    'name' => $uniqueTeamName,
                    'institution' => $institution,
                    'city' => $city,
                    'user_id' => $user->id,
                    'description' => "Equipo de robótica especializado en competencias amateur y profesionales."
                ]);
            }
        }
    }
} 