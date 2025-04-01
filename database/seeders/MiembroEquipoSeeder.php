<?php

namespace Database\Seeders;

use App\Models\Equipo;
use App\Models\MiembroEquipo;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MiembroEquipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los equipos
        $equipos = Equipo::all();
        
        foreach ($equipos as $equipo) {
            // Obtener el usuario propietario del equipo
            $usuario = User::find($equipo->user_id);
            
            if ($usuario) {
                // Crear al usuario como capitán del equipo
                MiembroEquipo::create([
                    'equipo_id' => $equipo->id,
                    'user_id' => $usuario->id,
                    'nombre' => $usuario->name,
                    'email' => $usuario->email,
                    'rol' => 'Capitán',
                    'es_capitan' => true,
                    'activo' => true,
                ]);
                
                // Crear 2-4 miembros adicionales para cada equipo
                $numMiembros = rand(2, 4);
                
                $roles = [
                    'Programador',
                    'Diseñador',
                    'Mecánico',
                    'Electrónico',
                    'Asistente',
                ];
                
                for ($i = 0; $i < $numMiembros; $i++) {
                    MiembroEquipo::create([
                        'equipo_id' => $equipo->id,
                        'nombre' => 'Miembro ' . ($i + 1) . ' de ' . $equipo->nombre,
                        'email' => 'miembro' . ($i + 1) . '@' . strtolower(str_replace(' ', '', $equipo->nombre)) . '.com',
                        'rol' => $roles[array_rand($roles)],
                        'es_capitan' => false,
                        'activo' => true,
                    ]);
                }
            }
        }
    }
}
