<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requisitos del Sistema - Copa Robótica 2025</title>
    <style>
        /* Estilos base */
        :root {
            --blue-600: #2563eb;
            --blue-700: #1d4ed8;
            --blue-800: #1e40af;
            --green-100: #dcfce7;
            --green-500: #22c55e;
            --green-600: #16a34a;
            --green-700: #15803d;
            --green-800: #166534;
            --red-100: #fee2e2;
            --red-500: #ef4444;
            --red-600: #dc2626;
            --red-700: #b91c1c;
            --red-800: #991b1b;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --purple-600: #9333ea;
            --yellow-600: #ca8a04;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-800);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Keyframes */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        .animate-pulse-slow {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        /* Utilidades */
        .transition-transform {
            transition-property: transform;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
        }
        
        .hover-scale-105:hover {
            transform: scale(1.05);
        }
        
        .container {
            width: 100%;
            margin-left: auto;
            margin-right: auto;
        }
        
        .max-w-5xl {
            max-width: 64rem;
        }
        
        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }
        
        .p-1 { padding: 0.25rem; }
        .p-2 { padding: 0.5rem; }
        .p-3 { padding: 0.75rem; }
        .p-4 { padding: 1rem; }
        .p-5 { padding: 1.25rem; }
        .p-6 { padding: 1.5rem; }
        .p-8 { padding: 2rem; }
        
        .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
        .px-8 { padding-left: 2rem; padding-right: 2rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
        
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 0.75rem; }
        .mt-8 { margin-top: 2rem; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 0.75rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mb-8 { margin-bottom: 2rem; }
        .mr-1 { margin-right: 0.25rem; }
        .mr-2 { margin-right: 0.5rem; }
        .mr-3 { margin-right: 0.75rem; }
        .ml-1 { margin-left: 0.25rem; }
        .ml-2 { margin-left: 0.5rem; }
        .ml-4 { margin-left: 1rem; }
        
        .flex { display: flex; }
        .flex-1 { flex: 1 1 0%; }
        .flex-col { flex-direction: column; }
        .flex-row { flex-direction: row; }
        .flex-grow { flex-grow: 1; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 0.5rem; }
        .gap-3 { gap: 0.75rem; }
        .gap-4 { gap: 1rem; }
        .space-y-1 > * + * { margin-top: 0.25rem; }
        .space-y-3 > * + * { margin-top: 0.75rem; }
        
        .grid { display: grid; }
        .grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
        
        .w-full { width: 100%; }
        .w-10 { width: 2.5rem; }
        .h-2 { height: 0.5rem; }
        .h-10 { height: 2.5rem; }
        
        .text-xs { font-size: 0.75rem; }
        .text-sm { font-size: 0.875rem; }
        .text-lg { font-size: 1.125rem; }
        .text-xl { font-size: 1.25rem; }
        .text-2xl { font-size: 1.5rem; }
        .text-3xl { font-size: 1.875rem; }
        
        .font-medium { font-weight: 500; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        
        .text-center { text-align: center; }
        
        /* Colores */
        .text-white { color: white; }
        .text-green-600 { color: var(--green-600); }
        .text-green-700 { color: var(--green-700); }
        .text-green-800 { color: var(--green-800); }
        .text-red-600 { color: var(--red-600); }
        .text-red-700 { color: var(--red-700); }
        .text-red-800 { color: var(--red-800); }
        .text-blue-600 { color: var(--blue-600); }
        .text-blue-800 { color: var(--blue-800); }
        .text-gray-500 { color: var(--gray-500); }
        .text-gray-600 { color: var(--gray-600); }
        .text-gray-700 { color: var(--gray-700); }
        .text-gray-800 { color: var(--gray-800); }
        .text-purple-600 { color: var(--purple-600); }
        .text-yellow-600 { color: var(--yellow-600); }
        
        .bg-green-100 { background-color: var(--green-100); }
        .bg-red-100 { background-color: var(--red-100); }
        .bg-blue-300 { background-color: #93c5fd; }
        .bg-blue-600 { background-color: var(--blue-600); }
        .bg-gray-50 { background-color: var(--gray-50); }
        .bg-gray-100 { background-color: var(--gray-100); }
        .bg-gray-200 { background-color: var(--gray-200); }
        .bg-gray-300 { background-color: var(--gray-300); }
        .bg-white { background-color: white; }
        
        .border { border-width: 1px; }
        .border-t { border-top-width: 1px; }
        .border-l-4 { border-left-width: 4px; }
        .border-gray-100 { border-color: var(--gray-100); }
        .border-gray-200 { border-color: var(--gray-200); }
        .border-green-500 { border-color: var(--green-500); }
        .border-red-500 { border-color: var(--red-500); }
        
        .rounded { border-radius: 0.25rem; }
        .rounded-lg { border-radius: 0.5rem; }
        .rounded-xl { border-radius: 0.75rem; }
        .rounded-full { border-radius: 9999px; }
        
        .shadow { box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }
        .shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
        .shadow-xl { box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        
        .opacity-90 { opacity: 0.9; }
        
        .cursor-not-allowed { cursor: not-allowed; }
        
        .list-disc { list-style-type: disc; }
        .pl-5 { padding-left: 1.25rem; }
        
        .hover\:bg-gray-100:hover { background-color: var(--gray-100); }
        .hover\:underline:hover { text-decoration: underline; }
        
        .transition { transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter; }
        .duration-200 { transition-duration: 200ms; }
        
        /* Header especial */
        .header-gradient {
            background: linear-gradient(to right, var(--blue-700), var(--blue-600));
        }
        
        .btn-gradient {
            background: linear-gradient(to right, var(--blue-600), var(--blue-700));
        }
        .btn-gradient:hover {
            background: linear-gradient(to right, var(--blue-700), var(--blue-800));
        }
        
        /* Para dispositivos medianos (tablet) */
        @media (min-width: 768px) {
            .md\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .md\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .md\:p-6 { padding: 1.5rem; }
            .md\:p-8 { padding: 2rem; }
        }
        
        /* Para dispositivos pequeños (móvil) */
        @media (min-width: 640px) {
            .sm\:flex-row { flex-direction: row; }
        }
    </style>
</head>
<body>
    <header class="header-gradient text-white p-6 shadow-lg">
        <div class="container mx-auto max-w-5xl">
            <h1 class="text-3xl font-bold">Copa Robótica 2025</h1>
            <p class="text-lg mt-2 opacity-90">Asistente de Instalación</p>
        </div>
    </header>

    <main class="flex-grow container mx-auto p-4 md:p-6 max-w-5xl">
        <!-- Pasos de instalación -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-8">
            <div class="w-full">
                <div class="flex items-center mb-2">
                    <div class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white rounded-full mr-3 shadow-md">
                        <span class="text-lg font-bold">1</span>
                    </div>
                    <span class="text-lg font-semibold">Requisitos del Sistema</span>
                </div>
                <div class="h-2 bg-blue-600 rounded-full"></div>
            </div>
            <div class="w-full">
                <div class="flex items-center mb-2">
                    <div class="w-10 h-10 flex items-center justify-center bg-gray-300 text-gray-600 rounded-full mr-3 shadow">
                        <span class="text-lg font-bold">2</span>
                    </div>
                    <span class="text-lg font-semibold text-gray-500">Configuración de Base de Datos</span>
                </div>
                <div class="h-2 bg-gray-300 rounded-full"></div>
            </div>
            <div class="w-full">
                <div class="flex items-center mb-2">
                    <div class="w-10 h-10 flex items-center justify-center bg-gray-300 text-gray-600 rounded-full mr-3 shadow">
                        <span class="text-lg font-bold">3</span>
                    </div>
                    <span class="text-lg font-semibold text-gray-500">Configuración de la Aplicación</span>
                </div>
                <div class="h-2 bg-gray-300 rounded-full"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 mb-6 border border-gray-100">
            <h2 class="text-2xl font-bold mb-6 text-blue-800 flex items-center">
                <span class="mr-3 text-blue-600">✓</span>Requisitos del Sistema
            </h2>
            
            <!-- PHP Version Card -->
            <div class="mb-8 bg-gray-50 rounded-lg p-5 border border-gray-100 transition-transform hover-scale-105">
                <h3 class="text-xl font-semibold mb-4 flex items-center">
                    <span class="text-purple-600 mr-2 text-2xl">PHP</span>
                    Versión de PHP
                </h3>
                <div class="flex items-center mb-2">
                    @if($requirements['php']['status'])
                        <div class="bg-green-100 text-green-800 p-3 rounded-lg flex items-center w-full border-l-4 border-green-500">
                            <span class="text-green-600 mr-3 text-xl">✓</span>
                            <div>
                                <span class="font-semibold">PHP {{ $requirements['php']['version'] }} o superior</span>
                                <p class="text-sm text-green-700 mt-1">
                                    Instalado: {{ $requirements['php']['current'] }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="bg-red-100 text-red-800 p-3 rounded-lg flex items-center w-full border-l-4 border-red-500">
                            <span class="text-red-600 mr-3 text-xl">⚠</span>
                            <div>
                                <span class="font-semibold">PHP {{ $requirements['php']['version'] }} o superior</span>
                                <p class="text-sm text-red-700 mt-1">
                                    Instalado: {{ $requirements['php']['current'] }}
                                </p>
                                <p class="text-sm mt-2">
                                    <a href="https://www.php.net/downloads" target="_blank" class="text-blue-600 hover:underline">
                                        Actualiza PHP a la versión requerida
                                    </a>
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- PHP Extensions Card -->
            <div class="mb-8 bg-gray-50 rounded-lg p-5 border border-gray-100 transition-transform hover-scale-105">
                <h3 class="text-xl font-semibold mb-4 flex items-center">
                    <span class="text-blue-600 mr-2">🧩</span>
                    Extensiones de PHP
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($requirements['extensions'] as $extension => $installed)
                        <div class="flex items-center p-3 {{ $installed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} rounded-lg border-l-4 {{ $installed ? 'border-green-500' : 'border-red-500' }}">
                            @if($installed)
                                <span class="mr-3 {{ $installed ? 'text-green-600' : 'text-red-600' }}">✓</span>
                            @else
                                <span class="mr-3 {{ $installed ? 'text-green-600' : 'text-red-600' }}">✗</span>
                            @endif
                            <div>
                                <span class="font-medium">{{ $extension }}</span>
                                @if(!$installed)
                                    <p class="text-xs mt-1">
                                        <a href="https://www.php.net/manual/es/extensions.alphabetical.php" target="_blank" class="text-blue-600 hover:underline">
                                            Cómo instalar
                                        </a>
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Directory Permissions Card -->
            <div class="mb-8 bg-gray-50 rounded-lg p-5 border border-gray-100 transition-transform hover-scale-105">
                <h3 class="text-xl font-semibold mb-4 flex items-center">
                    <span class="text-yellow-600 mr-2">📁</span>
                    Permisos de directorios
                </h3>
                <div class="space-y-3">
                    @foreach($requirements['permissions'] as $directory => $writable)
                        <div class="flex items-center p-3 {{ $writable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} rounded-lg border-l-4 {{ $writable ? 'border-green-500' : 'border-red-500' }}">
                            @if($writable)
                                <span class="mr-3 {{ $writable ? 'text-green-600' : 'text-red-600' }}">✓</span>
                            @else
                                <span class="mr-3 {{ $writable ? 'text-green-600' : 'text-red-600' }}">🔒</span>
                            @endif
                            <div class="flex-1">
                                <span class="font-medium">{{ $directory }}</span>
                                <p class="text-xs mt-1">
                                    {{ $writable ? 'Escritura permitida' : 'Sin permisos de escritura' }}
                                </p>
                            </div>
                            @if(!$writable)
                                <div class="bg-white text-xs text-gray-600 p-2 rounded">
                                    chmod 775 {{ $directory }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Server Configuration Card -->
            <div class="mb-8 bg-gray-50 rounded-lg p-5 border border-gray-100 transition-transform hover-scale-105">
                <h3 class="text-xl font-semibold mb-4 flex items-center">
                    <span class="text-blue-600 mr-2">⚙️</span>
                    Configuración del Servidor
                </h3>
                <div class="space-y-3">
                    <!-- Memory Limit -->
                    <div class="flex items-center p-3 {{ $requirements['server']['memory_limit']['status'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} rounded-lg border-l-4 {{ $requirements['server']['memory_limit']['status'] ? 'border-green-500' : 'border-yellow-500' }}">
                        @if($requirements['server']['memory_limit']['status'])
                            <span class="mr-3 text-green-600">✓</span>
                        @else
                            <span class="mr-3 text-yellow-600">⚠️</span>
                        @endif
                        <div class="flex-1">
                            <span class="font-medium">Límite de Memoria (memory_limit)</span>
                            <p class="text-xs mt-1">
                                Actual: {{ $requirements['server']['memory_limit']['current'] }} (Recomendado: {{ $requirements['server']['memory_limit']['recommended'] }})
                            </p>
                        </div>
                        @if(!$requirements['server']['memory_limit']['status'])
                            <div class="bg-white text-xs text-gray-600 p-2 rounded">
                                Edite php.ini: memory_limit = 256M
                            </div>
                        @endif
                    </div>

                    <!-- Upload Max Filesize -->
                    <div class="flex items-center p-3 {{ $requirements['server']['upload_max_filesize']['status'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} rounded-lg border-l-4 {{ $requirements['server']['upload_max_filesize']['status'] ? 'border-green-500' : 'border-yellow-500' }}">
                        @if($requirements['server']['upload_max_filesize']['status'])
                            <span class="mr-3 text-green-600">✓</span>
                        @else
                            <span class="mr-3 text-yellow-600">⚠️</span>
                        @endif
                        <div class="flex-1">
                            <span class="font-medium">Tamaño máximo de subida (upload_max_filesize)</span>
                            <p class="text-xs mt-1">
                                Actual: {{ $requirements['server']['upload_max_filesize']['current'] }} (Recomendado: {{ $requirements['server']['upload_max_filesize']['recommended'] }})
                            </p>
                        </div>
                        @if(!$requirements['server']['upload_max_filesize']['status'])
                            <div class="bg-white text-xs text-gray-600 p-2 rounded">
                                Edite php.ini: upload_max_filesize = 10M
                            </div>
                        @endif
                    </div>

                    <!-- Max Execution Time -->
                    <div class="flex items-center p-3 {{ $requirements['server']['max_execution_time']['status'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} rounded-lg border-l-4 {{ $requirements['server']['max_execution_time']['status'] ? 'border-green-500' : 'border-yellow-500' }}">
                        @if($requirements['server']['max_execution_time']['status'])
                            <span class="mr-3 text-green-600">✓</span>
                        @else
                            <span class="mr-3 text-yellow-600">⚠️</span>
                        @endif
                        <div class="flex-1">
                            <span class="font-medium">Tiempo máximo de ejecución (max_execution_time)</span>
                            <p class="text-xs mt-1">
                                Actual: {{ $requirements['server']['max_execution_time']['current'] }}s (Recomendado: {{ $requirements['server']['max_execution_time']['recommended'] }}s)
                            </p>
                        </div>
                        @if(!$requirements['server']['max_execution_time']['status'])
                            <div class="bg-white text-xs text-gray-600 p-2 rounded">
                                Edite php.ini: max_execution_time = 60
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if(!$canProceed)
                <div class="bg-red-100 text-red-800 p-4 rounded-lg border-l-4 border-red-500 mb-6 shadow-md">
                    <div class="flex">
                        <span class="text-red-600 text-2xl mr-3">⚠️</span>
                        <div>
                            <h4 class="font-bold text-lg">No se puede continuar</h4>
                            <p class="mt-1">Por favor corrija los problemas mencionados antes de continuar con la instalación.</p>
                            <div class="mt-3 bg-white rounded p-3 text-gray-700 text-sm">
                                <p class="font-medium mb-1">Posibles soluciones:</p>
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Instale las extensiones de PHP faltantes</li>
                                    <li>Actualice PHP a la versión mínima requerida</li>
                                    <li>Corrija los permisos de los directorios (chmod 775)</li>
                                    <li>Ajuste la configuración del servidor en php.ini</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-green-100 text-green-800 p-4 rounded-lg border-l-4 border-green-500 mb-6 shadow-md">
                    <div class="flex">
                        <span class="text-green-600 text-2xl mr-3">✅</span>
                        <div>
                            <h4 class="font-bold text-lg">¡Todo listo!</h4>
                            <p class="mt-1">Su sistema cumple con todos los requisitos. Puede continuar con la instalación.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="flex flex-col sm:flex-row justify-between gap-4">
            <a href="{{ route('installer.welcome') }}" 
               class="bg-white hover:bg-gray-100 text-gray-800 font-bold py-3 px-6 rounded-xl shadow-md transition duration-200 border border-gray-200 flex items-center justify-center">
                ← Anterior
            </a>
            
            @if($canProceed)
                <a href="{{ route('installer.database') }}" 
                   class="btn-gradient text-white font-bold py-3 px-8 rounded-xl shadow-md transition duration-200 flex items-center justify-center">
                    Continuar →
                </a>
            @else
                <button disabled 
                        class="bg-blue-300 text-white font-bold py-3 px-8 rounded-xl shadow-md cursor-not-allowed flex items-center justify-center">
                    Continuar →
                </button>
            @endif
        </div>
    </main>

    <footer class="bg-gray-100 p-6 text-center text-gray-600 border-t border-gray-200 mt-8">
        <p>&copy; {{ date('Y') }} Copa Robótica 2025. Todos los derechos reservados.</p>
    </footer>

    <script>
        // Añadir efecto de carga cuando se hace clic en continuar
        document.addEventListener('DOMContentLoaded', function() {
            const continueButton = document.querySelector('a[href="{{ route('installer.database') }}"]');
            if (continueButton) {
                continueButton.addEventListener('click', function() {
                    this.classList.add('animate-pulse-slow');
                    this.innerHTML = '⏳ Procesando...';
                });
            }
        });
    </script>
</body>
</html> 