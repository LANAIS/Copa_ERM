<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Base de Datos - Copa Robótica 2025</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        /* Mismos estilos que en requirements.blade.php */
        /* ... (incluir todos los estilos del archivo requirements.blade.php) ... */
        
        /* Estilos específicos para este formulario */
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        select, input { 
            width: 100%; 
            padding: 0.5rem 0.75rem; 
            border: 1px solid #d1d5db; 
            border-radius: 0.375rem; 
            margin-top: 0.25rem;
        }
        select:focus, input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        .info-box {
            background-color: #e0f2fe;
            border-left: 4px solid #0ea5e9;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.375rem;
        }
        .security-box {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.375rem;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-blue-600 text-white p-6 shadow-md">
        <h1 class="text-3xl font-bold">Copa Robótica 2025</h1>
        <p class="text-lg mt-2">Asistente de Instalación</p>
    </header>

    <main class="flex-grow container mx-auto p-6 max-w-4xl">
        <div class="flex mb-6">
            <div class="w-full">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 flex items-center justify-center bg-green-600 text-white rounded-full mr-3">✓</div>
                    <span class="text-lg font-semibold">Requisitos del Sistema</span>
                </div>
                <div class="h-1 bg-green-600"></div>
            </div>
            <div class="w-full">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 flex items-center justify-center bg-blue-600 text-white rounded-full mr-3">2</div>
                    <span class="text-lg font-semibold">Configuración de Base de Datos</span>
                </div>
                <div class="h-1 bg-blue-600"></div>
            </div>
            <div class="w-full">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 flex items-center justify-center bg-gray-300 text-gray-700 rounded-full mr-3">3</div>
                    <span class="text-lg font-semibold text-gray-500">Configuración de la Aplicación</span>
                </div>
                <div class="h-1 bg-gray-300"></div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <h2 class="text-2xl font-bold mb-6 text-blue-800">Configuración de Base de Datos</h2>
            
            @if($errors->any())
                <div class="bg-red-100 text-red-700 p-4 rounded-md border-l-4 border-red-500 mb-6">
                    <strong>Error:</strong> 
                    <ul class="mt-2 list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="info-box mb-6">
                <h4 class="font-semibold text-blue-900">Información importante</h4>
                <p class="text-sm text-blue-800 mt-1">Para continuar con la instalación, se requiere una base de datos MySQL. El sistema verificará si la base de datos existe y tiene las tablas necesarias.</p>
                <ul class="list-disc text-sm text-blue-800 pl-5 mt-2">
                    <li>Asegúrese de que el usuario tenga permisos para <strong>CREATE</strong>, <strong>ALTER</strong>, <strong>INSERT</strong>, <strong>UPDATE</strong> y <strong>DELETE</strong>.</li>
                    <li>Si la base de datos no existe, el sistema intentará crearla (se requiere permiso CREATE DATABASE).</li>
                    <li>Se recomienda utilizar una base de datos exclusiva y vacía para esta aplicación.</li>
                </ul>
            </div>

            <div class="security-box mb-6">
                <h4 class="font-semibold text-red-900">Seguridad</h4>
                <p class="text-sm text-red-800 mt-1">
                    Por razones de seguridad, se recomienda:
                </p>
                <ul class="list-disc text-sm text-red-800 pl-5 mt-2">
                    <li>Crear un usuario específico para esta aplicación con permisos limitados</li>
                    <li>No utilizar el usuario 'root' de MySQL</li>
                    <li>Utilizar una contraseña segura (letras, números y caracteres especiales)</li>
                </ul>
            </div>
            
            <form action="{{ route('installer.database.save') }}" method="post">
                @csrf
                
                <div class="form-group">
                    <label for="db_connection">Sistema de Base de Datos</label>
                    <select name="db_connection" id="db_connection" class="form-control mb-2" required>
                        <option value="mysql">MySQL / MariaDB</option>
                        <option value="pgsql">PostgreSQL</option>
                        <option value="sqlite">SQLite</option>
                    </select>
                    <p class="text-sm text-gray-500">Seleccione el sistema de base de datos que está utilizando. Se recomienda MySQL/MariaDB.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group db-field">
                        <label for="db_host">Servidor de base de datos</label>
                        <input type="text" name="db_host" id="db_host" value="localhost" class="form-control" required>
                        <p class="text-sm text-gray-500">Por lo general es "localhost"</p>
                    </div>

                    <div class="form-group db-field">
                        <label for="db_port">Puerto</label>
                        <input type="number" name="db_port" id="db_port" value="3306" class="form-control" required>
                        <p class="text-sm text-gray-500">MySQL: 3306, PostgreSQL: 5432</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="db_database">Nombre de la base de datos</label>
                    <input type="text" name="db_database" id="db_database" value="coparobotica" class="form-control" required>
                    <p class="text-sm text-gray-500">El nombre de la base de datos donde se guardarán las tablas</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group db-field">
                        <label for="db_username">Usuario de base de datos</label>
                        <input type="text" name="db_username" id="db_username" class="form-control" required>
                        <p class="text-sm text-gray-500">Usuario con permisos para esta base de datos</p>
                    </div>

                    <div class="form-group db-field">
                        <label for="db_password">Contraseña</label>
                        <input type="password" name="db_password" id="db_password" class="form-control">
                        <p class="text-sm text-gray-500">Contraseña del usuario de base de datos</p>
                    </div>
                </div>
                
                <div class="flex justify-between mt-8">
                    <a href="{{ route('installer.requirements') }}" 
                        class="bg-white hover:bg-gray-100 text-gray-800 font-bold py-3 px-6 rounded-xl shadow-md transition duration-200 border border-gray-200 flex items-center justify-center">
                        ← Anterior
                    </a>
                    
                    <button type="submit" 
                            class="btn-gradient text-white font-bold py-3 px-8 rounded-xl shadow-md transition duration-200 flex items-center justify-center">
                        Verificar Conexión
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer class="bg-gray-200 p-6 text-center text-gray-600">
        <p>&copy; {{ date('Y') }} Copa Robótica 2025. Todos los derechos reservados.</p>
    </footer>

    <script>
        // Toggle campos según el tipo de base de datos
        document.addEventListener('DOMContentLoaded', function() {
            const dbConnectionSelect = document.getElementById('db_connection');
            const dbFields = document.querySelectorAll('.db-field');
            
            function toggleDbFields() {
                const isSqlite = dbConnectionSelect.value === 'sqlite';
                dbFields.forEach(field => {
                    field.style.display = isSqlite ? 'none' : 'block';
                });
                
                // Ajustar validación de requeridos
                if (isSqlite) {
                    document.getElementById('db_host').removeAttribute('required');
                    document.getElementById('db_port').removeAttribute('required');
                    document.getElementById('db_username').removeAttribute('required');
                } else {
                    document.getElementById('db_host').setAttribute('required', 'required');
                    document.getElementById('db_port').setAttribute('required', 'required');
                    document.getElementById('db_username').setAttribute('required', 'required');
                }
            }
            
            dbConnectionSelect.addEventListener('change', toggleDbFields);
            toggleDbFields();
        });
    </script>
</body>
</html> 