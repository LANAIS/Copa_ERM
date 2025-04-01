@extends('layouts.competitor')

@section('content')
<div>
    <h1 class="text-2xl font-bold mb-6">Bienvenido, {{ Auth::user()->name }}</h1>
    
    <!-- Tarjetas de estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Mis Robots</p>
                    <p class="text-2xl font-bold">{{ $robots->count() }}</p>
                </div>
            </div>
            <a href="{{ route('competitor.robots.index') }}" class="mt-4 inline-block text-sm text-blue-500 hover:text-blue-700">Ver todos →</a>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-purple-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Mis Equipos</p>
                    <p class="text-2xl font-bold">{{ $equipos->count() }}</p>
                </div>
            </div>
            <a href="{{ route('competitor.equipos.index') }}" class="mt-4 inline-block text-sm text-purple-500 hover:text-purple-700">Ver todos →</a>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-green-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Inscripciones</p>
                    <p class="text-2xl font-bold">{{ $inscripciones->count() }}</p>
                </div>
            </div>
            <a href="{{ route('competitor.inscripciones.index') }}" class="mt-4 inline-block text-sm text-green-500 hover:text-green-700">Ver todas →</a>
        </div>
    </div>
    
    <!-- Acciones rápidas -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-lg font-semibold mb-4">Acciones rápidas</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('competitor.robots.create') }}" class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                <div class="p-2 rounded-full bg-blue-500 text-white mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <span>Nuevo Robot</span>
            </a>
            
            <a href="{{ route('competitor.equipos.create') }}" class="flex items-center p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                <div class="p-2 rounded-full bg-purple-500 text-white mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <span>Nuevo Equipo</span>
            </a>
            
            <a href="{{ route('competitor.inscripciones.create') }}" class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition">
                <div class="p-2 rounded-full bg-green-500 text-white mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <span>Nueva Inscripción</span>
            </a>
        </div>
    </div>
    
    <!-- Sección de inscripciones recientes -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold mb-4">Inscripciones Recientes</h2>
            
            @if($inscripciones->count() > 0)
                <div class="space-y-4">
                    @foreach($inscripciones->take(5) as $inscripcion)
                        <div class="border-b border-gray-200 pb-3">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-medium">{{ $inscripcion->competition->nombre ?? 'Competencia' }}</p>
                                    <p class="text-sm text-gray-600">Robot: {{ $inscripcion->robot->nombre ?? 'Sin robot' }}</p>
                                    <p class="text-sm text-gray-600">Equipo: {{ $inscripcion->equipo->nombre ?? 'Sin equipo' }}</p>
                                </div>
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    @if($inscripcion->status === 'approved') bg-green-100 text-green-800
                                    @elseif($inscripcion->status === 'rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst($inscripcion->status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if($inscripciones->count() > 5)
                    <div class="mt-4 text-center">
                        <a href="{{ route('competitor.inscripciones.index') }}" class="text-sm text-blue-500 hover:text-blue-700">
                            Ver todas las inscripciones
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center py-6 text-gray-500">
                    <p>No tienes inscripciones todavía</p>
                    <a href="{{ route('competitor.inscripciones.create') }}" class="mt-2 inline-block text-sm text-blue-500 hover:text-blue-700">
                        Inscribirse a una competencia
                    </a>
                </div>
            @endif
        </div>
        
        <!-- Sección de competencias activas -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold mb-4">Competencias Activas</h2>
            
            @if($competencias->count() > 0)
                <div class="space-y-4">
                    @foreach($competencias->take(5) as $competencia)
                        <div class="border-b border-gray-200 pb-3">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-medium">{{ $competencia->name }}</p>
                                    <p class="text-sm text-gray-600">
                                        {{ $competencia->year }}
                                    </p>
                                </div>
                                <a href="{{ route('competitor.inscripciones.create', ['competition_id' => $competencia->id]) }}" 
                                   class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs hover:bg-blue-200">
                                    Inscribirse
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if($competencias->count() > 5)
                    <div class="mt-4 text-center">
                        <a href="{{ route('competitor.inscripciones.create') }}" class="text-sm text-blue-500 hover:text-blue-700">
                            Ver todas las competencias
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center py-6 text-gray-500">
                    <p>No hay competencias activas en este momento</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 