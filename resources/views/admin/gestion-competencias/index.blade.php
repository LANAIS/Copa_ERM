@extends('layouts.app')

@section('content')
<div class="container py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gestión de Competencias</h1>
        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
            Volver a Dashboard
        </a>
    </div>
    
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif
    
    <!-- Estadísticas -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="bg-blue-600 text-white px-4 py-3">
            <h2 class="text-lg font-medium">Panel de Control</h2>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-3 md:grid-cols-6 gap-4">
                <div class="bg-gray-100 p-3 rounded">
                    <span class="block text-xl font-bold text-center">{{ $stats['total_eventos'] }}</span>
                    <span class="block text-sm text-center text-gray-600">Eventos</span>
                </div>
                <div class="bg-green-100 p-3 rounded">
                    <span class="block text-xl font-bold text-center">{{ $stats['eventos_activos'] }}</span>
                    <span class="block text-sm text-center text-gray-600">Activos</span>
                </div>
                <div class="bg-blue-100 p-3 rounded">
                    <span class="block text-xl font-bold text-center">{{ $stats['total_categorias'] }}</span>
                    <span class="block text-sm text-center text-gray-600">Categorías</span>
                </div>
                <div class="bg-indigo-100 p-3 rounded">
                    <span class="block text-xl font-bold text-center">{{ $stats['categorias_activas'] }}</span>
                    <span class="block text-sm text-center text-gray-600">Activas</span>
                </div>
                <div class="bg-purple-100 p-3 rounded">
                    <span class="block text-xl font-bold text-center">{{ $stats['inscripciones'] }}</span>
                    <span class="block text-sm text-center text-gray-600">Inscripciones</span>
                </div>
                <div class="bg-yellow-100 p-3 rounded">
                    <span class="block text-xl font-bold text-center">{{ $stats['homologaciones_pendientes'] }}</span>
                    <span class="block text-sm text-center text-gray-600">Homologaciones</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Eventos activos -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-blue-600 text-white px-4 py-3">
            <h2 class="text-lg font-medium">Eventos Activos</h2>
        </div>
        <div class="p-4">
            @if($eventos->isEmpty())
                <div class="text-center py-4">
                    <p class="text-gray-500">No hay eventos activos</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($eventos as $evento)
                        <div class="border rounded-lg overflow-hidden bg-white hover:shadow-lg transition-shadow">
                            <div class="p-4 border-b bg-gray-50">
                                <h3 class="font-bold text-lg">{{ $evento->nombre }}</h3>
                                <p class="text-sm text-gray-600">{{ $evento->lugar }}</p>
                            </div>
                            
                            <div class="p-4 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Inicio:</span>
                                    <span>{{ $evento->fecha_inicio->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Fin:</span>
                                    <span>{{ $evento->fecha_fin->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Fechas:</span>
                                    <span>{{ $evento->fechas->count() }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Categorías:</span>
                                    <span>{{ $evento->categorias->count() }}</span>
                                </div>
                                
                                <div class="mt-4">
                                    <a href="{{ route('admin.gestion-competencias.evento', $evento->id) }}" 
                                       class="block w-full text-center py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                        Gestionar Evento
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 