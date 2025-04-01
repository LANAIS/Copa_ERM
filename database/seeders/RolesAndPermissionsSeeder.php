<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reiniciar roles y permisos cacheados
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear roles si no existen
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $judgeRole = Role::firstOrCreate(['name' => 'judge']);
        $competitorRole = Role::firstOrCreate(['name' => 'competitor']);

        // Crear permisos para cada área
        // Administradores
        $adminPermissions = [
            'manage_users',
            'manage_roles',
            'manage_events',
            'manage_categories',
            'manage_teams',
            'manage_registrations',
            'manage_brackets',
            'manage_scores',
        ];

        // Jueces
        $judgePermissions = [
            'judge_homologations',
            'judge_competitions',
            'record_scores',
            'manage_brackets',
        ];

        // Competidores
        $competitorPermissions = [
            'manage_own_teams',
            'manage_own_robots',
            'register_to_events',
            'view_results',
        ];

        // Crear los permisos y asignarlos a los roles
        foreach ($adminPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
            $adminRole->givePermissionTo($permission);
        }

        foreach ($judgePermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
            $judgeRole->givePermissionTo($permission);
        }

        foreach ($competitorPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
            $competitorRole->givePermissionTo($permission);
        }

        // Crear un usuario administrador por defecto si no existe
        $admin = User::firstOrCreate(
            ['email' => 'admin@coparobotica.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );
        
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Crear un usuario juez por defecto si no existe
        $judge = User::firstOrCreate(
            ['email' => 'juez@coparobotica.com'],
            [
                'name' => 'Juez',
                'password' => Hash::make('password'),
            ]
        );
        
        if (!$judge->hasRole('judge')) {
            $judge->assignRole('judge');
        }

        // Crear un usuario competidor por defecto si no existe
        $competitor = User::firstOrCreate(
            ['email' => 'competidor@coparobotica.com'],
            [
                'name' => 'Competidor',
                'password' => Hash::make('password'),
            ]
        );
        
        if (!$competitor->hasRole('competitor')) {
            $competitor->assignRole('competitor');
        }
    }
}
