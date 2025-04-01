<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea un usuario administrador';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Verificar si el usuario ya existe
        $existingUser = User::where('email', $email)->first();
        
        if ($existingUser) {
            $this->info("El usuario con email {$email} ya existe. Actualizando la contraseña y asignando rol de administrador.");
            $existingUser->password = Hash::make($password);
            $existingUser->save();
            $user = $existingUser;
        } else {
            // Crear nuevo usuario
            $user = User::create([
                'name' => 'Administrador',
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $this->info("Usuario administrador creado con éxito: {$email}");
        }

        // Verificar si existe el rol de administrador, si no, crearlo
        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
            $this->info("Rol de administrador creado");
        }

        // Asignar rol de administrador
        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
            $this->info("Rol de administrador asignado al usuario");
        }

        return Command::SUCCESS;
    }
} 