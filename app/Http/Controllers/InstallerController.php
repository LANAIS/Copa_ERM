<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use PDO;
use PDOException;

class InstallerController extends Controller
{
    /**
     * Muestra la vista de bienvenida al instalador
     */
    public function welcome()
    {
        // Si ya está instalado, redirigir al home
        if (file_exists(storage_path('app/installed.json'))) {
            return redirect('/');
        }

        // Crear un archivo temporal para indicar que estamos en proceso de instalación
        File::put(storage_path('app/installing'), '');

        return view('installer.welcome');
    }

    /**
     * Comprueba los requisitos del sistema
     */
    public function requirements()
    {
        if (file_exists(storage_path('app/installed.json'))) {
            return redirect('/');
        }

        $requirements = [
            'php' => [
                'version' => '8.2.0',
                'current' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '8.2.0', '>=')
            ],
            'extensions' => [
                'BCMath' => extension_loaded('bcmath'),
                'Ctype' => extension_loaded('ctype'),
                'Fileinfo' => extension_loaded('fileinfo'),
                'JSON' => extension_loaded('json'),
                'Mbstring' => extension_loaded('mbstring'),
                'OpenSSL' => extension_loaded('openssl'),
                'PDO' => extension_loaded('pdo'),
                'PDO_MySQL' => extension_loaded('pdo_mysql'),
                'Tokenizer' => extension_loaded('tokenizer'),
                'XML' => extension_loaded('xml'),
                'Zip' => extension_loaded('zip'),
            ],
            'permissions' => [
                'storage/app' => is_writable(storage_path('app')),
                'storage/framework' => is_writable(storage_path('framework')),
                'storage/logs' => is_writable(storage_path('logs')),
                'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
                '.env' => is_writable(base_path('.env')),
                'public/storage' => is_link(public_path('storage')) || is_writable(public_path()),
            ],
            'server' => [
                'memory_limit' => $this->checkMemoryLimit(),
                'upload_max_filesize' => $this->checkUploadMaxFilesize(),
                'max_execution_time' => $this->checkMaxExecutionTime(),
            ]
        ];

        $canProceed = $requirements['php']['status'] && 
                      !in_array(false, $requirements['extensions']) && 
                      !in_array(false, $requirements['permissions']) &&
                      !in_array(false, $requirements['server']);

        return view('installer.requirements', compact('requirements', 'canProceed'));
    }

    /**
     * Verifica el límite de memoria de PHP
     */
    private function checkMemoryLimit()
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->returnBytes($memoryLimit);
        
        // Mínimo recomendado: 256MB
        return [
            'recommended' => '256M',
            'current' => $memoryLimit,
            'status' => $memoryLimitBytes >= 268435456 || $memoryLimitBytes === -1
        ];
    }

    /**
     * Verifica el tamaño máximo de subida de archivos
     */
    private function checkUploadMaxFilesize()
    {
        $uploadMaxFilesize = ini_get('upload_max_filesize');
        $uploadMaxFilesizeBytes = $this->returnBytes($uploadMaxFilesize);
        
        // Mínimo recomendado: 10MB
        return [
            'recommended' => '10M',
            'current' => $uploadMaxFilesize,
            'status' => $uploadMaxFilesizeBytes >= 10485760
        ];
    }

    /**
     * Verifica el tiempo máximo de ejecución
     */
    private function checkMaxExecutionTime()
    {
        $maxExecutionTime = ini_get('max_execution_time');
        
        // Mínimo recomendado: 60 segundos
        return [
            'recommended' => '60',
            'current' => $maxExecutionTime,
            'status' => $maxExecutionTime >= 60 || $maxExecutionTime === 0
        ];
    }

    /**
     * Convierte valores como '128M' a bytes
     */
    private function returnBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (int) $val;
        
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        
        return $val;
    }

    /**
     * Muestra el formulario de configuración de base de datos
     */
    public function database()
    {
        if (file_exists(storage_path('app/installed.json'))) {
            return redirect('/');
        }

        return view('installer.database');
    }

    /**
     * Procesa el formulario de configuración de base de datos
     */
    public function databaseSave(Request $request)
    {
        $request->validate([
            'db_connection' => 'required|in:mysql,pgsql,sqlite',
            'db_host' => 'required_unless:db_connection,sqlite',
            'db_port' => 'required_unless:db_connection,sqlite',
            'db_database' => 'required',
            'db_username' => 'required_unless:db_connection,sqlite',
            'db_password' => 'nullable',
        ]);

        try {
            // Probar la conexión
            if ($request->db_connection === 'sqlite') {
                $path = database_path($request->db_database);
                if (!file_exists($path)) {
                    File::put($path, '');
                }
                $connectionValid = true;
                $userPermissions = ['create' => true, 'insert' => true, 'update' => true, 'delete' => true, 'alter' => true];
            } else {
                $connectionParams = [
                    'driver' => $request->db_connection,
                    'host' => $request->db_host,
                    'port' => $request->db_port,
                    'database' => $request->db_database,
                    'username' => $request->db_username,
                    'password' => $request->db_password,
                ];

                try {
                    // Primero verificar si la conexión funciona sin especificar base de datos
                    $dsn = "{$connectionParams['driver']}:host={$connectionParams['host']};port={$connectionParams['port']}";
                    $pdo = new PDO($dsn, $connectionParams['username'], $connectionParams['password']);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Comprobar permisos de usuario (CREATE, ALTER, DROP, etc.)
                    $userPermissions = $this->checkMySQLUserPermissions($pdo, $connectionParams['username'], $connectionParams['host']);
                    
                    // Si no tiene permisos suficientes, lanzar error
                    if (!$userPermissions['create'] || !$userPermissions['alter']) {
                        return redirect()->back()->withErrors([
                            'connection' => 'El usuario no tiene permisos suficientes. Se requieren permisos CREATE, ALTER, INSERT, UPDATE, DELETE.'
                        ]);
                    }
                    
                    // Luego verificar si la base de datos existe
                    $dbExists = false;
                    $databases = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
                    if (in_array($connectionParams['database'], $databases)) {
                        $dbExists = true;
                    }
                    
                    // Si la base de datos no existe, intentar crearla
                    if (!$dbExists) {
                        if (!$userPermissions['create']) {
                            return redirect()->back()->withErrors([
                                'connection' => 'El usuario no tiene permisos para crear bases de datos. Contacte con su administrador para crear la base de datos manualmente.'
                            ]);
                        }
                        
                        try {
                            $pdo->exec("CREATE DATABASE `{$connectionParams['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                            $dbExists = true;
                        } catch (\Exception $e) {
                            return redirect()->back()->withErrors([
                                'connection' => 'Error al crear la base de datos: ' . $e->getMessage()
                            ]);
                        }
                    }
                    
                    // Verificar si podemos conectar a la base de datos
                    if ($dbExists) {
                        try {
                            // Ahora probar la conexión a la base de datos específica
                            $dsn = "{$connectionParams['driver']}:host={$connectionParams['host']};port={$connectionParams['port']};dbname={$connectionParams['database']}";
                            $dbPdo = new PDO($dsn, $connectionParams['username'], $connectionParams['password']);
                            $dbPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            
                            // Verificar si la base de datos está vacía o tiene tablas
                            $stmt = $dbPdo->query("SHOW TABLES");
                            $tableCount = count($stmt->fetchAll(PDO::FETCH_COLUMN));
                            
                            if ($tableCount > 0) {
                                // Si hay tablas, verificar si es una instalación anterior de este sistema
                                $isSameSystem = false;
                                
                                try {
                                    $stmt = $dbPdo->query("SHOW TABLES LIKE 'migrations'");
                                    if ($stmt->rowCount() > 0) {
                                        // Si hay tabla migrations, verificar si tiene nuestras migraciones
                                        $stmt = $dbPdo->query("SELECT * FROM migrations WHERE migration LIKE '2023_%'");
                                        $isSameSystem = $stmt->rowCount() > 0;
                                    }
                                } catch (\Exception $e) {
                                    // Ignorar errores, asumimos que no es el mismo sistema
                                }
                                
                                if (!$isSameSystem) {
                                    return redirect()->back()->withErrors([
                                        'connection' => 'La base de datos no está vacía y parece contener tablas de otro sistema. Por favor, use una base de datos vacía o borre las tablas existentes.'
                                    ]);
                                }
                            }
                            
                            // Todo correcto, crear la tabla de sesiones
                            $this->createSessionsTable($dbPdo);
                        } catch (\Exception $e) {
                            return redirect()->back()->withErrors([
                                'connection' => 'Error al conectar a la base de datos: ' . $e->getMessage()
                            ]);
                        }
                    } else {
                        return redirect()->back()->withErrors([
                            'connection' => 'La base de datos no existe y no se pudo crear automáticamente.'
                        ]);
                    }
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors([
                        'connection' => 'Error al conectar al servidor de base de datos: ' . $e->getMessage()
                    ]);
                }
                
                // Si llegamos hasta aquí, la conexión es válida
                $connectionValid = true;
            }

            // Si la conexión es válida, guardar los datos en .env
            if (isset($connectionValid) && $connectionValid) {
                // Guardar información de verificación de base de datos
                File::put(storage_path('app/db_verified.json'), json_encode([
                    'verified_at' => now()->toDateTimeString(),
                    'connection' => $request->db_connection,
                    'host' => $request->db_host,
                    'port' => $request->db_port,
                    'database' => $request->db_database,
                    'permissions' => $userPermissions
                ]));

                // Actualizar archivo .env
                $envContent = File::get(base_path('.env'));

                $envContent = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=' . $request->db_connection, $envContent);
                
                if ($request->db_connection !== 'sqlite') {
                    $envContent = preg_replace('/DB_HOST=.*/', 'DB_HOST=' . $request->db_host, $envContent);
                    $envContent = preg_replace('/DB_PORT=.*/', 'DB_PORT=' . $request->db_port, $envContent);
                    $envContent = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=' . $request->db_username, $envContent);
                    $envContent = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=' . $request->db_password, $envContent);
                }
                
                $envContent = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=' . $request->db_database, $envContent);

                File::put(base_path('.env'), $envContent);

                // Limpiar caché
                Artisan::call('config:clear');

                // Cambiar sesión a cookie durante la instalación para evitar errores
                config(['session.driver' => 'cookie']);

                return redirect()->route('installer.app');
            }
        } catch (PDOException $e) {
            return redirect()->back()->withErrors(['connection' => 'No se pudo conectar a la base de datos: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['connection' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Verifica los permisos del usuario de MySQL
     */
    private function checkMySQLUserPermissions($pdo, $username, $host)
    {
        $permissions = [
            'create' => false,
            'insert' => false,
            'update' => false,
            'delete' => false,
            'alter' => false
        ];
        
        try {
            // Verificar permisos con SHOW GRANTS
            $stmt = $pdo->query("SHOW GRANTS FOR CURRENT_USER()");
            $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($grants as $grant) {
                if (strpos($grant, 'ALL PRIVILEGES') !== false) {
                    // Si tiene todos los privilegios
                    return array_fill_keys(array_keys($permissions), true);
                }
                
                // Comprobar cada permiso
                foreach (array_keys($permissions) as $permission) {
                    if (strpos(strtoupper($grant), strtoupper($permission)) !== false) {
                        $permissions[$permission] = true;
                    }
                }
            }
        } catch (\Exception $e) {
            // Si no podemos obtener permisos, asumir que todo está bien
            // (esto puede suceder si el usuario no tiene permisos para ejecutar SHOW GRANTS)
            return array_fill_keys(array_keys($permissions), true);
        }
        
        return $permissions;
    }

    /**
     * Crea la tabla de sesiones de forma manual
     */
    private function createSessionsTable($pdo)
    {
        $tableExists = false;
        
        try {
            // Verificar si la tabla ya existe
            $result = $pdo->query("SHOW TABLES LIKE 'sessions'");
            $tableExists = $result->rowCount() > 0;
        } catch (\Exception $e) {
            // Si hay error, asumimos que no existe
            $tableExists = false;
        }
        
        if (!$tableExists) {
            try {
                // Crear la tabla sessions (estructura basada en la migración de Laravel)
                // La estructura es idéntica a la que crea Laravel en sus migraciones
                $query = "CREATE TABLE `sessions` (
                    `id` VARCHAR(191) NOT NULL PRIMARY KEY,
                    `user_id` BIGINT UNSIGNED NULL,
                    `ip_address` VARCHAR(45) NULL,
                    `user_agent` TEXT NULL,
                    `payload` LONGTEXT NOT NULL,
                    `last_activity` INT NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                $pdo->exec($query);
                
                // Crear los índices de la tabla
                $pdo->exec("CREATE INDEX `sessions_user_id_index` ON `sessions` (`user_id`)");
                $pdo->exec("CREATE INDEX `sessions_last_activity_index` ON `sessions` (`last_activity`)");
                
                // Guardar un indicador de que la tabla de sesiones fue creada manualmente
                File::put(storage_path('app/sessions_table_created.flag'), date('Y-m-d H:i:s'));
            } catch (\Exception $e) {
                // Si falla, no es crítico, la migración lo intentará posteriormente
                File::put(storage_path('app/sessions_table_error.txt'), $e->getMessage());
            }
        }
    }

    /**
     * Muestra el formulario de configuración de la aplicación
     */
    public function app()
    {
        if (file_exists(storage_path('app/installed.json'))) {
            return redirect('/');
        }

        // Verificar si la base de datos ha sido verificada
        if (!file_exists(storage_path('app/db_verified.json'))) {
            return redirect()->route('installer.database')
                ->withErrors(['warning' => 'Por favor, configure la base de datos primero.']);
        }

        return view('installer.app');
    }

    /**
     * Procesa el formulario de configuración de la aplicación
     */
    public function appSave(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'app_locale' => 'required|in:es,en',
            'admin_email' => 'required|email',
            'admin_password' => 'required|min:8|confirmed',
            'admin_password_confirmation' => 'required'
        ]);

        try {
            // Verificar si la base de datos ha sido verificada
            if (!file_exists(storage_path('app/db_verified.json'))) {
                return redirect()->route('installer.database')
                    ->withErrors(['warning' => 'Por favor, configure la base de datos primero.']);
            }

            // Cambiar a sesiones en cookie durante la instalación
            // Esto es necesario porque la tabla de sesiones aún no existe
            config(['session.driver' => 'cookie']);
            
            // Actualizar .env
            $envContent = File::get(base_path('.env'));
            $envContent = preg_replace('/APP_NAME=.*/', 'APP_NAME="' . $request->app_name . '"', $envContent);
            $envContent = preg_replace('/APP_URL=.*/', 'APP_URL=' . $request->app_url, $envContent);
            $envContent = preg_replace('/APP_LOCALE=.*/', 'APP_LOCALE=' . $request->app_locale, $envContent);
            
            // Mantener sesiones como file hasta que la instalación esté completa
            // Después será cambiado a database durante el primer inicio de sesión
            $envContent = preg_replace('/SESSION_DRIVER=.*/', 'SESSION_DRIVER=file', $envContent);
            
            File::put(base_path('.env'), $envContent);

            // Limpiar caché
            Artisan::call('config:clear');

            // Establecer la conexión a la base de datos y ejecutar migraciones
            try {
                // Comprobar si la tabla de sesiones ya existe y eliminar la migración si es necesario
                $this->handleSessionsTableMigration();
                
                // Ejecutar migraciones
                $migrateOutput = '';
                Artisan::call('migrate:fresh', ['--force' => true]);
                $migrateOutput = Artisan::output();
                
                // Guardar log de las migraciones
                File::put(storage_path('app/migration_log.txt'), $migrateOutput);
                
                // Ejecutar seeders
                $seedOutput = '';
                Artisan::call('db:seed', ['--force' => true]);
                $seedOutput = Artisan::output();
                
                // Guardar log de los seeders
                File::append(storage_path('app/migration_log.txt'), PHP_EOL . PHP_EOL . "--- SEEDERS ---" . PHP_EOL . $seedOutput);
                
                // Ahora que tenemos tablas, podemos cambiar el driver de sesión a database
                $envContent = File::get(base_path('.env'));
                $envContent = preg_replace('/SESSION_DRIVER=.*/', 'SESSION_DRIVER=database', $envContent);
                File::put(base_path('.env'), $envContent);
            } catch (\Exception $e) {
                // Si hay error en migraciones/seeders, guardar el error y revertir
                File::put(storage_path('app/migration_error.txt'), $e->getMessage() . PHP_EOL . $e->getTraceAsString());
                
                return redirect()->back()->withErrors([
                    'error' => 'Error durante la migración de base de datos: ' . $e->getMessage()
                ]);
            }

            // Crear usuario administrador
            try {
                Artisan::call('app:create-admin', [
                    'email' => $request->admin_email,
                    'password' => $request->admin_password,
                ]);
            } catch (\Exception $e) {
                // Si hay error al crear el administrador, registrar pero continuar
                File::put(storage_path('app/admin_error.txt'), $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }

            // Crear directorio de storage si no existe
            if (!file_exists(public_path('storage'))) {
                try {
                    Artisan::call('storage:link');
                } catch (\Exception $e) {
                    // Registrar el error pero continuar
                    File::put(storage_path('app/storage_link_error.txt'), $e->getMessage());
                }
            }

            // Eliminar los archivos temporales de verificación
            if (file_exists(storage_path('app/db_verified.json'))) {
                File::delete(storage_path('app/db_verified.json'));
            }

            // Eliminar el archivo temporal de instalación
            if (file_exists(storage_path('app/installing'))) {
                File::delete(storage_path('app/installing'));
            }

            // Crear archivo de instalación
            File::put(storage_path('app/installed.json'), json_encode([
                'installed_at' => now()->toDateTimeString(),
                'version' => config('app.version', '1.0.0'),
                'admin_email' => $request->admin_email,
                'app_name' => $request->app_name,
                'app_url' => $request->app_url,
                'app_locale' => $request->app_locale,
            ]));

            return redirect()->route('installer.finished');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Error durante la instalación: ' . $e->getMessage()]);
        }
    }

    /**
     * Maneja la migración de la tabla de sesiones para evitar conflictos
     */
    private function handleSessionsTableMigration()
    {
        // Buscar el archivo de migración que crea la tabla sessions
        $migrationFiles = glob(database_path('migrations/*_create_sessions_table.php'));
        
        if (!empty($migrationFiles)) {
            $migrationFile = $migrationFiles[0];
            
            // Verificar si la tabla sessions ya existe en la base de datos
            $tableExists = false;
            
            try {
                $tableExists = Schema::hasTable('sessions');
            } catch (\Exception $e) {
                // Si hay error al comprobar, asumimos que no existe
                $tableExists = false;
            }
            
            if ($tableExists) {
                // Si la tabla ya existe, modificar el archivo de migración para que no intente crearla de nuevo
                $content = File::get($migrationFile);
                
                // Modificar el método up() para que no haga nada si la tabla ya existe
                $modifiedContent = preg_replace(
                    '/public function up\(\)(.*?)}/s',
                    'public function up()
    {
        // La tabla de sesiones ya fue creada manualmente, omitir esta migración
        if (Schema::hasTable(\'sessions\')) {
            return;
        }
        
        Schema::create(\'sessions\', function (Blueprint $table) {
            $table->string(\'id\')->primary();
            $table->foreignId(\'user_id\')->nullable()->index();
            $table->string(\'ip_address\', 45)->nullable();
            $table->text(\'user_agent\')->nullable();
            $table->longText(\'payload\');
            $table->integer(\'last_activity\')->index();
        });
    }',
                    $content
                );
                
                File::put($migrationFile, $modifiedContent);
            }
        }
    }

    /**
     * Muestra la página de finalización
     */
    public function finished()
    {
        // Asegurarse de tener la configuración de sesiones adecuada
        // para evitar errores de conexión restablecida
        config(['session.driver' => 'cookie']);
        
        if (!file_exists(storage_path('app/installed.json'))) {
            return redirect()->route('installer.welcome');
        }

        $installInfo = json_decode(File::get(storage_path('app/installed.json')), true);

        // Verificar si hay errores y preparar los mensajes
        $hasErrors = file_exists(storage_path('app/migration_error.txt')) || 
                     file_exists(storage_path('app/admin_error.txt')) ||
                     file_exists(storage_path('app/storage_link_error.txt'));
                     
        $errorMessages = [];
        
        if (file_exists(storage_path('app/migration_error.txt'))) {
            $errorMessages[] = 'Hubo un problema con algunas migraciones.';
        }
        
        if (file_exists(storage_path('app/admin_error.txt'))) {
            $errorMessages[] = 'Hubo un problema al crear el usuario administrador.';
        }
        
        if (file_exists(storage_path('app/storage_link_error.txt'))) {
            $errorMessages[] = 'No se pudo crear el enlace simbólico para storage.';
        }

        return view('installer.finished', [
            'installInfo' => $installInfo,
            'hasErrors' => $hasErrors,
            'errorMessages' => $errorMessages
        ]);
    }
} 