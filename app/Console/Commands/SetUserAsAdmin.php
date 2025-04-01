<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetUserAsAdmin extends Command
{
    protected $signature = 'user:set-admin {email}';
    protected $description = 'Establece un usuario como administrador';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("No se encontró ningún usuario con el email: {$email}");
            return 1;
        }

        $user->role = 'admin';
        $user->save();

        $this->info("El usuario {$email} ha sido establecido como administrador.");
        return 0;
    }
} 