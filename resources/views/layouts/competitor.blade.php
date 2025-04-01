<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Copa Robótica') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Menú lateral -->
        <div class="w-64 bg-blue-800 text-white shadow-xl hidden md:block">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-white mb-6">Panel Competidor</h1>
                
                <nav class="space-y-4">
                    <a href="{{ route('competitor.index') }}" class="flex items-center space-x-2 p-2 rounded-lg {{ request()->routeIs('competitor.index') ? 'bg-blue-900' : 'hover:bg-blue-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                        </svg>
                        <span>Inicio</span>
                    </a>
                    
                    <a href="{{ route('competitor.robots.index') }}" class="flex items-center space-x-2 p-2 rounded-lg {{ request()->routeIs('competitor.robots.*') ? 'bg-blue-900' : 'hover:bg-blue-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                        </svg>
                        <span>Mis Robots</span>
                    </a>
                    
                    <a href="{{ route('competitor.equipos.index') }}" class="flex items-center space-x-2 p-2 rounded-lg {{ request()->routeIs('competitor.equipos.*') ? 'bg-blue-900' : 'hover:bg-blue-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                        </svg>
                        <span>Mis Equipos</span>
                    </a>
                    
                    <a href="{{ route('competitor.inscripciones.index') }}" class="flex items-center space-x-2 p-2 rounded-lg {{ request()->routeIs('competitor.inscripciones.*') ? 'bg-blue-900' : 'hover:bg-blue-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                        </svg>
                        <span>Inscripciones</span>
                    </a>
                </nav>
            </div>
            
            <div class="absolute bottom-0 left-0 right-0 p-4 bg-blue-900">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-white text-blue-800 flex items-center justify-center font-bold">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 truncate">
                        <p class="text-sm font-medium">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-blue-300 truncate">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                
                <div class="mt-3 border-t border-blue-700 pt-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left flex items-center space-x-2 text-sm text-blue-300 hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Barra superior móvil -->
            <header class="bg-white shadow md:hidden">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center space-x-3">
                        <button id="sidebar-toggle" class="text-gray-500 hover:text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-lg font-semibold">Panel Competidor</h1>
                    </div>
                </div>
            </header>

            <!-- Menú móvil (oculto por defecto) -->
            <div id="mobile-menu" class="fixed inset-0 bg-gray-800 bg-opacity-75 z-50 transform -translate-x-full transition-transform duration-300 md:hidden">
                <div class="w-3/4 max-w-xs bg-blue-800 h-full flex flex-col">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-white">Panel Competidor</h2>
                            <button id="close-sidebar" class="text-white hover:text-gray-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <nav class="space-y-4">
                            <a href="{{ route('competitor.index') }}" class="flex items-center space-x-2 p-2 rounded-lg {{ request()->routeIs('competitor.index') ? 'bg-blue-900' : 'hover:bg-blue-700' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                                </svg>
                                <span class="text-white">Inicio</span>
                            </a>
                            
                            <a href="{{ route('competitor.robots.index') }}" class="flex items-center space-x-2 p-2 rounded-lg {{ request()->routeIs('competitor.robots.*') ? 'bg-blue-900' : 'hover:bg-blue-700' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-white">Mis Robots</span>
                            </a>
                            
                            <a href="{{ route('competitor.equipos.index') }}" class="flex items-center space-x-2 p-2 rounded-lg {{ request()->routeIs('competitor.equipos.*') ? 'bg-blue-900' : 'hover:bg-blue-700' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                </svg>
                                <span class="text-white">Mis Equipos</span>
                            </a>
                            
                            <a href="{{ route('competitor.inscripciones.index') }}" class="flex items-center space-x-2 p-2 rounded-lg {{ request()->routeIs('competitor.inscripciones.*') ? 'bg-blue-900' : 'hover:bg-blue-700' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-white">Inscripciones</span>
                            </a>
                        </nav>
                    </div>
                    
                    <div class="mt-auto p-4 bg-blue-900">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-white text-blue-800 flex items-center justify-center font-bold">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div class="flex-1 truncate">
                                <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-blue-300 truncate">{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                        
                        <div class="mt-3 border-t border-blue-700 pt-3">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center space-x-2 text-sm text-blue-300 hover:text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Cerrar sesión</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50">
                @if(session('success'))
                    <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md" role="alert">
                        <div class="flex items-center">
                            <svg class="h-6 w-6 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md" role="alert">
                        <div class="flex items-center">
                            <svg class="h-6 w-6 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Toggle mobile menu
        document.getElementById('sidebar-toggle')?.addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.remove('-translate-x-full');
        });
        
        document.getElementById('close-sidebar')?.addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.add('-translate-x-full');
        });
    </script>
</body>
</html> 