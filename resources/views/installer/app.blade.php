<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de la Aplicación - Copa Robótica 2025</title>
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
                    <div class="w-8 h-8 flex items-center justify-center bg-green-600 text-white rounded-full mr-3">✓</div>
                    <span class="text-lg font-semibold">Configuración de Base de Datos</span>
                </div>
                <div class="h-1 bg-green-600"></div>
            </div>
            <div class="w-full">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 flex items-center justify-center bg-blue-600 text-white rounded-full mr-3">3</div>
                    <span class="text-lg font-semibold">Configuración de la Aplicación</span>
                </div>
                <div class="h-1 bg-blue-600"></div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <h2 class="text-2xl font-bold mb-6 text-blue-800">Configuración de la Aplicación</h2>
            
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
            
            <form action="{{ route('installer.app.save') }}" method="post">
                @csrf
                
                <div class="form-group">
                    <label for="app_name">Nombre de la aplicación</label>
                    <input type="text" id="app_name" name="app_name" value="Copa Robótica 2025" class="form-control">
                    <p class="text-sm text-gray-500 mt-1">Este nombre se mostrará en el título de las páginas y en los correos electrónicos</p>
                </div>

                <div class="form-group">
                    <label for="app_url">URL de la aplicación</label>
                    <input type="url" id="app_url" name="app_url" value="{{ url('/') }}" class="form-control">
                    <p class="text-sm text-gray-500 mt-1">La URL completa de su aplicación sin barra al final (ej: https://coparobotica.com)</p>
                </div>

                <div class="form-group">
                    <label for="app_locale">Idioma</label>
                    <select id="app_locale" name="app_locale" class="form-control">
                        <option value="es">Español</option>
                        <option value="en">English</option>
                    </select>
                </div>

                <div class="border-t border-gray-200 pt-6 mt-8 mb-6">
                    <h3 class="text-xl font-semibold mb-4 flex items-center">
                        <span class="text-blue-600 mr-2">👤</span>
                        Cuenta de Administrador
                    </h3>
                    
                    <div class="form-group">
                        <label for="admin_email">Correo electrónico</label>
                        <input type="email" id="admin_email" name="admin_email" class="form-control" value="{{ old('admin_email') }}">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_password">Contraseña</label>
                        <input type="password" id="admin_password" name="admin_password" class="form-control">
                        <p class="text-sm text-gray-500 mt-1">Mínimo 8 caracteres</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_password_confirmation">Confirmar contraseña</label>
                        <input type="password" id="admin_password_confirmation" name="admin_password_confirmation" class="form-control">
                        <p class="text-sm text-gray-500 mt-1">Repita la contraseña para confirmar</p>
                    </div>
                </div>
            
                <div class="flex flex-col sm:flex-row justify-between gap-4 mt-8">
                    <a href="{{ route('installer.database') }}" 
                       class="bg-white hover:bg-gray-100 text-gray-800 font-bold py-3 px-6 rounded-xl shadow-md transition duration-200 border border-gray-200 flex items-center justify-center">
                        ← Anterior
                    </a>
                    
                    <button type="submit" 
                            class="btn-gradient text-white font-bold py-3 px-8 rounded-xl shadow-md transition duration-200 flex items-center justify-center">
                        Finalizar Instalación →
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer class="bg-gray-200 p-6 text-center text-gray-600">
        <p>&copy; {{ date('Y') }} Copa Robótica 2025. Todos los derechos reservados.</p>
    </footer>

    <script>
        // Verificación de coincidencia de contraseñas
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const passwordField = document.getElementById('admin_password');
            const confirmField = document.getElementById('admin_password_confirmation');
            
            form.addEventListener('submit', function(e) {
                if (passwordField.value !== confirmField.value) {
                    alert('Las contraseñas no coinciden. Por favor, inténtalo de nuevo.');
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html> 