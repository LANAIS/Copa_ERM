@extends('layouts.competitor')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Mis Robots</h1>
        <a href="{{ route('competitor.robots.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-md flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Crear Robot
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form action="{{ route('competitor.robots.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nombre del robot" 
                       class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="categoria" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                <select name="categoria" id="categoria" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todas las categorías</option>
                    <option value="sumo" {{ request('categoria') == 'sumo' ? 'selected' : '' }}>Sumo</option>
                    <option value="seguidor" {{ request('categoria') == 'seguidor' ? 'selected' : '' }}>Seguidor de Línea</option>
                    <option value="lucha" {{ request('categoria') == 'lucha' ? 'selected' : '' }}>Lucha</option>
                </select>
            </div>
            <div>
                <label for="equipo" class="block text-sm font-medium text-gray-700 mb-1">Equipo</label>
                <select name="equipo_id" id="equipo" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos los equipos</option>
                    @foreach($equipos ?? [] as $equipo)
                        <option value="{{ $equipo->id }}" {{ request('equipo_id') == $equipo->id ? 'selected' : '' }}>
                            {{ $equipo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm">
                    Filtrar
                </button>
                <a href="{{ route('competitor.robots.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    @if($robots->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($robots as $robot)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="h-48 bg-gray-200 relative">
                        @if($robot->imagen)
                            <img src="{{ Storage::url($robot->imagen) }}" alt="{{ $robot->nombre }}" class="w-full h-full object-cover">
                        @else
                            <div class="flex items-center justify-center h-full bg-blue-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                        
                        <!-- Acciones rápidas flotantes -->
                        <div class="absolute top-2 right-2 flex space-x-1">
                            <a href="{{ route('competitor.robots.edit', $robot->id) }}" class="p-1.5 bg-white rounded-full shadow hover:bg-gray-100 transition-colors" title="Editar robot">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-700" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                            </a>
                            
                            <form action="{{ route('competitor.robots.destroy', $robot->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este robot?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 bg-white rounded-full shadow hover:bg-red-100 transition-colors" title="Eliminar robot">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                        
                        <!-- Categoría como badge -->
                        <div class="absolute bottom-2 left-2">
                            <span class="px-2 py-1 bg-blue-600 text-white text-xs font-bold rounded-full shadow-md">
                                {{ ucfirst($robot->categoria) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-5">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $robot->nombre }}</h3>
                        <p class="text-gray-600 text-sm mb-3 h-12 overflow-hidden">{{ $robot->descripcion }}</p>
                        
                        @if($robot->equipo)
                            <div class="flex items-center mb-3 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-purple-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                </svg>
                                <span class="text-gray-700">{{ $robot->equipo->nombre }}</span>
                            </div>
                        @endif
                        
                        <!-- Botones de acción -->
                        <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 gap-2">
                            <a href="{{ route('competitor.robots.show', $robot->id) }}" 
                               class="text-center py-2 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition-colors text-sm font-medium">
                                Ver detalles
                            </a>
                            
                            <a href="{{ route('competitor.inscripciones.create', ['robot_id' => $robot->id]) }}" 
                               class="text-center py-2 bg-green-50 text-green-600 rounded hover:bg-green-100 transition-colors text-sm font-medium">
                                Inscribir
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-500 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">No tienes robots todavía</h3>
            <p class="text-gray-600 mb-6">Crea tu primer robot para poder inscribirte en competencias</p>
            <a href="{{ route('competitor.robots.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Crear mi primer robot
            </a>
        </div>
    @endif
</div>
@endsection 