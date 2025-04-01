<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased bg-gray-50 dark:bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Copa Robótica 2025') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Filament Styles -->
    <link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/filament/forms/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/filament/support/support.css') }}">
    
    <!-- Fonts -->
    <style>
        html {
            font-family: 'Poppins', sans-serif !important;
        }
    </style>

    <!-- Custom Styles -->
    @yield('styles')
</head>
<body class="filament-body bg-gray-50 font-sans antialiased dark:bg-gray-900 dark:text-white">
    <div class="filament-app-layout flex min-h-screen w-full overflow-x-clip">
        <!-- Sidebar (imita el sidebar de Filament) -->
        <aside class="filament-sidebar fixed inset-y-0 left-0 z-20 flex flex-col h-screen border-r bg-white dark:bg-gray-800 dark:border-gray-700" style="width: 20rem">
            <header class="filament-sidebar-header h-16 p-4 flex items-center border-b dark:border-gray-700">
                <a href="{{ url('/admin') }}" class="flex items-center gap-2">
                    <img src="{{ \App\Models\SiteConfig::getLogo() }}" alt="Logo" class="h-10">
                    <span class="font-bold text-lg">Copa Robótica 2025</span>
                </a>
            </header>
            <nav class="filament-sidebar-nav flex-1 overflow-y-auto py-4">
                <ul class="px-4 space-y-2">
                    <li>
                        <a href="{{ url('/admin') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/admin') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg {{ Request::path() == 'brackets' ? 'bg-amber-100 text-amber-600 dark:bg-amber-700 dark:text-amber-100' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z" />
                            </svg>
                            <span>Ver Bracket</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/admin') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg {{ Request::path() == 'admin/brackets/admin' ? 'bg-amber-100 text-amber-600' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                            </svg>
                            <span>Administrar Bracket</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/admin') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                            </svg>
                            <span>Gestión de Competencias</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <footer class="filament-sidebar-footer h-16 p-4 flex items-center border-t dark:border-gray-700">
                <span class="text-sm text-gray-500 dark:text-gray-400">© {{ date('Y') }} Copa Robótica</span>
            </footer>
        </aside>

        <!-- Contenido principal -->
        <div class="filament-main flex flex-col gap-y-6 w-screen flex-1 md:ml-80">
            <!-- Barra superior -->
            <header class="filament-main-topbar h-16 w-full p-4 bg-white shadow flex items-center justify-between dark:bg-gray-800">
                <h1 class="text-xl font-bold">
                    @yield('title', 'Bracket')
                </h1>
                <div class="flex items-center gap-2">
                    @auth
                    <div class="flex items-center space-x-2">
                        <span>{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </form>
                    </div>
                    @else
                    <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">Iniciar sesión</a>
                    @endauth
                </div>
            </header>

            <!-- Contenido principal -->
            <div class="filament-main-content flex-1 p-4 md:p-6">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Filament Scripts -->
    <script src="{{ asset('js/filament/filament/app.js') }}"></script>
    <script src="{{ asset('js/filament/support/support.js') }}"></script>
    
    <!-- Custom Scripts -->
    @yield('scripts')
</body>
</html> 