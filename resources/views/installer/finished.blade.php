<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación Completada - Copa Robótica 2025</title>
    <style>
        /* Mismos estilos que en requirements.blade.php y database.blade.php */
        /* ... (incluir todos los estilos del archivo requirements.blade.php) ... */
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
        <!-- Progress Steps -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-8">
            <div class="w-full">
                <div class="flex items-center mb-2">
                    <div class="w-10 h-10 flex items-center justify-center bg-green-600 text-white rounded-full mr-3 shadow-md">
                        <span class="text-lg">✓</span>
                    </div>
                    <span class="text-lg font-semibold">Requisitos del Sistema</span>
                </div>
                <div class="h-2 bg-green-600 rounded-full"></div>
            </div>
            <div class="w-full">
                <div class="flex items-center mb-2">
                    <div class="w-10 h-10 flex items-center justify-center bg-green-600 text-white rounded-full mr-3 shadow-md">
                        <span class="text-lg">✓</span>
                    </div>
                    <span class="text-lg font-semibold">Configuración de Base de Datos</span>
                </div>
                <div class="h-2 bg-green-600 rounded-full"></div>
            </div>
            <div class="w-full">
                <div class="flex items-center mb-2">
                    <div class="w-10 h-10 flex items-center justify-center bg-green-600 text-white rounded-full mr-3 shadow-md">
                        <span class="text-lg">✓</span>
                    </div>
                    <span class="text-lg font-semibold">Configuración de la Aplicación</span>
                </div>
                <div class="h-2 bg-green-600 rounded-full"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 mb-6 border border-gray-100">
            <div class="flex items-center justify-center mb-8">
                @if($hasErrors)
                    <div class="bg-yellow-100 text-yellow-800 p-4 rounded-full h-24 w-24 flex items-center justify-center">
                        <span class="text-5xl">⚠️</span>
                    </div>
                @else
                    <div class="bg-green-100 text-green-800 p-4 rounded-full h-24 w-24 flex items-center justify-center">
                        <span class="text-5xl">✅</span>
                    </div>
                @endif
            </div>
            
            <h2 class="text-2xl font-bold mb-6 text-center text-green-800">¡Instalación Completada con Éxito!</h2>
            
            @if($hasErrors)
                <div class="bg-yellow-100 text-yellow-800 p-4 rounded-lg border-l-4 border-yellow-500 mb-6">
                    <div class="flex">
                        <span class="text-yellow-600 text-xl mr-3">⚠️</span>
                        <div>
                            <h4 class="font-bold">Se encontraron algunos problemas durante la instalación</h4>
                            <p class="mt-2">La instalación ha finalizado, pero se detectaron algunas advertencias:</p>
                            <ul class="list-disc pl-5 mt-2">
                                @foreach($errorMessages as $errorMessage)
                                    <li>{{ $errorMessage }}</li>
                                @endforeach
                            </ul>
                            <p class="mt-2">Revise los logs para más detalles o contacte con soporte técnico.</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-green-100 text-green-800 p-4 rounded-lg border-l-4 border-green-500 mb-6">
                    <div class="flex">
                        <span class="text-green-600 text-xl mr-3">✅</span>
                        <div>
                            <h4 class="font-bold">Instalación completada con éxito</h4>
                            <p class="mt-2">¡Enhorabuena! La aplicación Copa Robótica 2025 ha sido instalada correctamente.</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="mb-8 bg-blue-50 rounded-lg p-5 border border-blue-100">
                <h3 class="text-xl font-semibold mb-4 flex items-center">
                    <span class="text-blue-600 mr-2">📋</span>
                    Resumen de la instalación
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-blue-800">Información de la aplicación</h4>
                        <ul class="mt-2 space-y-2">
                            <li class="flex items-center">
                                <span class="text-gray-600 min-w-32">Nombre:</span>
                                <span class="font-medium">{{ $installInfo['app_name'] ?? 'Copa Robótica 2025' }}</span>
                            </li>
                            <li class="flex items-center">
                                <span class="text-gray-600 min-w-32">URL:</span>
                                <span class="font-medium">{{ $installInfo['app_url'] ?? url('/') }}</span>
                            </li>
                            <li class="flex items-center">
                                <span class="text-gray-600 min-w-32">Idioma:</span>
                                <span class="font-medium">{{ $installInfo['app_locale'] ?? 'es' }}</span>
                            </li>
                            <li class="flex items-center">
                                <span class="text-gray-600 min-w-32">Versión:</span>
                                <span class="font-medium">{{ $installInfo['version'] ?? '1.0.0' }}</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-blue-800">Acceso al sistema</h4>
                        <ul class="mt-2 space-y-2">
                            <li class="flex items-center">
                                <span class="text-gray-600 min-w-32">Email:</span>
                                <span class="font-medium">{{ $installInfo['admin_email'] ?? 'admin@example.com' }}</span>
                            </li>
                            <li class="flex items-center">
                                <span class="text-gray-600 min-w-32">Instalado:</span>
                                <span class="font-medium">{{ date('d/m/Y H:i', strtotime($installInfo['installed_at'] ?? now())) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg border border-green-200 p-5 shadow-sm transition hover:shadow-md">
                    <h3 class="text-lg font-semibold text-green-700 mb-3 flex items-center">
                        <span class="mr-2">🏠</span> Acceso al panel
                    </h3>
                    <p class="text-gray-600 mb-4">Ya puede acceder al panel de administración con las credenciales que configuró durante la instalación.</p>
                    <a href="{{ url('/admin') }}" 
                       class="btn-gradient text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-200 flex items-center justify-center w-full">
                        Ir al Panel de Administración
                    </a>
                </div>
                
                <div class="bg-white rounded-lg border border-blue-200 p-5 shadow-sm transition hover:shadow-md">
                    <h3 class="text-lg font-semibold text-blue-700 mb-3 flex items-center">
                        <span class="mr-2">🌐</span> Sitio público
                    </h3>
                    <p class="text-gray-600 mb-4">Visite el sitio web para ver la interfaz pública de la Copa Robótica 2025.</p>
                    <a href="{{ url('/') }}" 
                       class="bg-white border border-blue-500 text-blue-700 font-bold py-2 px-4 rounded-lg shadow-md transition duration-200 flex items-center justify-center w-full hover:bg-blue-50">
                        Ir al Sitio Web
                    </a>
                </div>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Pasos recomendados</h3>
                <ul class="space-y-2">
                    <li class="flex items-start">
                        <span class="text-green-600 mr-2">✓</span>
                        <span>Personalizar la configuración del sitio desde el panel de administración</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-600 mr-2">✓</span>
                        <span>Configurar los ajustes de correo electrónico para notificaciones</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-600 mr-2">✓</span>
                        <span>Revisar y configurar las categorías de competición</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-600 mr-2">✓</span>
                        <span>Crear y asignar roles de jueces y organizadores</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="flex justify-center">
            <a href="{{ url('/') }}" 
               class="btn-gradient text-white font-bold py-3 px-8 rounded-xl shadow-md transition duration-200 flex items-center justify-center">
                Ir al Inicio
            </a>
        </div>
    </main>

    <footer class="bg-gray-100 p-6 text-center text-gray-600 border-t border-gray-200 mt-8">
        <p>&copy; {{ date('Y') }} Copa Robótica 2025. Todos los derechos reservados.</p>
    </footer>
</body>
</html> 