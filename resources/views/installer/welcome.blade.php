<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - Copa Robótica 2025</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-blue-600 text-white p-6 shadow-md">
        <h1 class="text-3xl font-bold">Copa Robótica 2025</h1>
        <p class="text-lg mt-2">Asistente de Instalación</p>
    </header>

    <main class="flex-grow container mx-auto p-6 max-w-4xl">
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <h2 class="text-2xl font-bold mb-6 text-blue-800">¡Bienvenido al Instalador!</h2>
            
            <p class="mb-4">Este asistente te guiará a través del proceso de instalación de Copa Robótica 2025 en tu servidor.</p>
            
            <p class="mb-4">En los siguientes pasos:</p>
            <ul class="list-disc pl-8 mb-6">
                <li class="mb-2">Verificaremos que tu servidor cumpla con los requisitos técnicos</li>
                <li class="mb-2">Configurarás la conexión a la base de datos</li>
                <li class="mb-2">Personalizarás la configuración básica de la aplicación</li>
                <li class="mb-2">Crearás una cuenta de administrador</li>
            </ul>
            
            <p class="bg-yellow-100 p-4 rounded-md border-l-4 border-yellow-500">
                <strong>Importante:</strong> Antes de continuar, asegúrate de tener preparada la información de tu base de datos (servidor, puerto, usuario, contraseña).
            </p>
        </div>
        
        <div class="flex justify-end">
            <a href="{{ route('installer.requirements') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-200">
                Continuar
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block ml-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </main>

    <footer class="bg-gray-200 p-6 text-center text-gray-600">
        <p>&copy; {{ date('Y') }} Copa Robótica 2025. Todos los derechos reservados.</p>
    </footer>
</body>
</html> 