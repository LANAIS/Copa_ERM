<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignDefaultRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-default-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigna roles por defecto a los usuarios existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Verifica si existen los roles necesarios
        $this->checkRoles();
        
        // Obtener todos los usuarios
        $users = User::all();
        
        $this->info('Asignando roles por defecto a ' . $users->count() . ' usuarios...');
        
        $competitors = 0;
        
        foreach ($users as $user) {
            // Omitir usuarios que ya tienen un rol
            if ($user->hasRole('admin') || $user->hasRole('judge') || $user->hasRole('competitor')) {
                continue;
            }
            
            // Asignar rol de competidor por defecto
            $user->assignRole('competitor');
            $competitors++;
        }
        
        $this->info("Proceso completado.");
        $this->info("Usuarios actualizados con rol de competidor: $competitors");
        
        return Command::SUCCESS;
    }
    
    /**
     * Verificar si existen los roles necesarios y crearlos si es necesario
     */
    private function checkRoles()
    {
        $roles = ['admin', 'judge', 'competitor'];
        
        foreach ($roles as $roleName) {
            if (!Role::where('name', $roleName)->exists()) {
                $this->info("Creando rol: $roleName");
                Role::create(['name' => $roleName]);
            }
        }
    }
}
