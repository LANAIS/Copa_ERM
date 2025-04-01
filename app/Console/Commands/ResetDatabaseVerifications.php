<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetDatabaseVerifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-database-verifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando limpieza de archivos de verificación de instalación...');
        
        $files = [
            storage_path('app/db_verified.json'),
            storage_path('app/installing'),
            storage_path('app/installed.json'),
            storage_path('app/migration_log.txt'),
            storage_path('app/migration_error.txt'),
            storage_path('app/admin_error.txt'),
            storage_path('app/storage_link_error.txt'),
            storage_path('app/sessions_table_created.flag'),
            storage_path('app/sessions_table_error.txt')
        ];
        
        $count = 0;
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
                $this->line("- Eliminado: " . basename($file));
                $count++;
            }
        }
        
        // Elimina o modifica la tabla de sesiones si existe
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                $this->info('Eliminando tabla de sesiones...');
                \Illuminate\Support\Facades\Schema::drop('sessions');
                $this->line('- Tabla de sesiones eliminada correctamente');
            }
        } catch (\Exception $e) {
            $this->error('Error al eliminar tabla de sesiones: ' . $e->getMessage());
        }
        
        // Verificar y crear la base de datos si no existe
        $this->info('Verificando la base de datos...');
        $dbName = env('DB_DATABASE', 'coparobotica_misiones');
        $dbHost = env('DB_HOST', 'localhost');
        $dbPort = env('DB_PORT', '3306');
        $dbUser = env('DB_USERNAME', 'root');
        $dbPass = env('DB_PASSWORD', '');
        
        try {
            // Conectar sin especificar base de datos
            $dsn = "mysql:host={$dbHost};port={$dbPort}";
            $pdo = new \PDO($dsn, $dbUser, $dbPass);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Verificar si la base de datos existe
            $databases = $pdo->query("SHOW DATABASES")->fetchAll(\PDO::FETCH_COLUMN);
            $dbExists = in_array($dbName, $databases);
            
            if (!$dbExists) {
                $this->info("La base de datos '{$dbName}' no existe, creándola...");
                $pdo->exec("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $this->line("- Base de datos '{$dbName}' creada correctamente");
            } else {
                $this->line("- Base de datos '{$dbName}' ya existe");
                
                // Conectar a la base de datos para verificar las tablas
                $dbDsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName}";
                $dbPdo = new \PDO($dbDsn, $dbUser, $dbPass);
                $dbPdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                // Verificar si hay tablas
                $stmt = $dbPdo->query("SHOW TABLES");
                $tableCount = count($stmt->fetchAll(\PDO::FETCH_COLUMN));
                
                if ($tableCount > 0) {
                    if ($this->confirm("La base de datos '{$dbName}' contiene {$tableCount} tablas. ¿Desea eliminarlas?", true)) {
                        $this->info("Eliminando todas las tablas de '{$dbName}'...");
                        $dbPdo->exec("SET FOREIGN_KEY_CHECKS = 0");
                        
                        $tables = $dbPdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
                        foreach ($tables as $table) {
                            $dbPdo->exec("DROP TABLE `{$table}`");
                            $this->line("- Tabla '{$table}' eliminada");
                        }
                        
                        $dbPdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                        $this->info("Todas las tablas han sido eliminadas");
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error('Error al verificar/crear la base de datos: ' . $e->getMessage());
        }
        
        // Actualizar .env para usar sesiones de archivo
        try {
            $envContent = file_get_contents(base_path('.env'));
            $envContent = preg_replace('/SESSION_DRIVER=.*/', 'SESSION_DRIVER=file', $envContent);
            file_put_contents(base_path('.env'), $envContent);
            $this->line('- Configuración de sesiones actualizada a "file"');
        } catch (\Exception $e) {
            $this->error('Error al actualizar .env: ' . $e->getMessage());
        }
        
        // Limpiar caché de configuración
        $this->info('Limpiando caché de configuración...');
        try {
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            $this->line('- Caché de configuración limpiada correctamente');
        } catch (\Exception $e) {
            $this->error('Error al limpiar caché: ' . $e->getMessage());
        }
        
        if ($count > 0) {
            $this->info('¡Limpieza completada! Se eliminaron ' . $count . ' archivos de verificación.');
            $this->info('Ahora puede reintentar la instalación desde el principio.');
        } else {
            $this->info('No se encontraron archivos de verificación para eliminar.');
        }
        
        return 0;
    }
}
