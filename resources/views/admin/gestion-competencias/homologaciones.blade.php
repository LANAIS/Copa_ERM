@extends('layouts.app')

@section('content')
<div class="container py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Homologaciones - {{ $categoria->categoria->nombre }}</h1>
            <p class="text-gray-600">{{ $categoria->evento->nombre }} | {{ $categoria->evento->lugar }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.gestion-competencias.categoria', $categoria->id) }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                Volver a la Categoría
            </a>
        </div>
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
    
    <!-- Resumen de homologaciones -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="bg-yellow-600 text-white px-4 py-3">
            <h2 class="text-lg font-medium">Resumen de Homologaciones</h2>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div class="bg-gray-100 p-3 rounded">
                    <span class="block text-xl font-bold text-center">{{ $stats['total'] }}</span>
                    <span class="block text-sm text-center text-gray-600">Total</span>
                </div>
                <div class="bg-yellow-100 p-3 rounded">
                    <span class="block text-xl font-bold text-center">{{ $stats['pendientes'] }}</span>
                    <span class="block text-sm text-center text-gray-600">Pendientes</span>
                </div>
                <div class="bg-green-100 p-3 rounded">
                    <span class="block text-xl font-bold text-center">{{ $stats['aprobadas'] }}</span>
                    <span class="block text-sm text-center text-gray-600">Aprobadas</span>
                </div>
                <div class="bg-red-100 p-3 rounded">
                    <span class="block text-xl font-bold text-center">{{ $stats['rechazadas'] }}</span>
                    <span class="block text-sm text-center text-gray-600">Rechazadas</span>
                </div>
            </div>
            
            <!-- Barra de progreso -->
            <div class="mt-4">
                <div class="flex justify-between text-sm mb-1">
                    <span>Progreso de Homologación</span>
                    <span>{{ $stats['aprobadas'] + $stats['rechazadas'] }}/{{ $stats['total'] }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="flex h-4 rounded-full overflow-hidden">
                        <div class="bg-green-500 h-4" style="width: {{ $stats['total'] > 0 ? ($stats['aprobadas'] / $stats['total']) * 100 : 0 }}%"></div>
                        <div class="bg-red-500 h-4" style="width: {{ $stats['total'] > 0 ? ($stats['rechazadas'] / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div class="flex text-xs mt-1">
                    <span class="text-green-600 mr-2">■ Aprobadas ({{ $stats['total'] > 0 ? round(($stats['aprobadas'] / $stats['total']) * 100) : 0 }}%)</span>
                    <span class="text-red-600 mr-2">■ Rechazadas ({{ $stats['total'] > 0 ? round(($stats['rechazadas'] / $stats['total']) * 100) : 0 }}%)</span>
                    <span class="text-gray-500">■ Pendientes ({{ $stats['total'] > 0 ? round(($stats['pendientes'] / $stats['total']) * 100) : 0 }}%)</span>
                </div>
            </div>
            
            @if($stats['pendientes'] == 0 && $stats['total'] > 0)
                <div class="mt-4 text-center">
                    <a href="{{ route('admin.gestion-competencias.categoria', $categoria->id) }}" 
                       class="inline-block px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Volver y continuar
                    </a>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Lista de robots para homologar -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-yellow-600 text-white px-4 py-3 flex justify-between items-center">
            <h2 class="text-lg font-medium">Robots para Homologar</h2>
            
            <div class="flex space-x-2">
                <button type="button" class="px-3 py-1 bg-gray-100 text-gray-800 text-sm rounded hover:bg-gray-200" onclick="filtrarHomologaciones('todos')">
                    Todos
                </button>
                <button type="button" class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded hover:bg-yellow-200" onclick="filtrarHomologaciones('pendiente')">
                    Pendientes
                </button>
                <button type="button" class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded hover:bg-green-200" onclick="filtrarHomologaciones('aprobado')">
                    Aprobados
                </button>
                <button type="button" class="px-3 py-1 bg-red-100 text-red-800 text-sm rounded hover:bg-red-200" onclick="filtrarHomologaciones('rechazado')">
                    Rechazados
                </button>
            </div>
        </div>
        <div class="p-4">
            @if($categoria->homologaciones->isEmpty())
                <div class="text-center py-4">
                    <p class="text-gray-500">No hay robots para homologar en esta categoría</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="homologaciones-container">
                    @foreach($categoria->homologaciones as $homologacion)
                        <div class="border rounded-lg overflow-hidden bg-white hover:shadow-md transition-shadow homologacion-card" 
                             data-estado="{{ $homologacion->estado }}">
                            <div class="p-3 border-b bg-gray-50 flex justify-between items-center">
                                <h3 class="font-bold">{{ $homologacion->robot->nombre }}</h3>
                                
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($homologacion->estado == 'pendiente') bg-yellow-100 text-yellow-800
                                    @elseif($homologacion->estado == 'aprobado') bg-green-100 text-green-800
                                    @elseif($homologacion->estado == 'rechazado') bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($homologacion->estado) }}
                                </span>
                            </div>
                            
                            <div class="p-3">
                                <div class="text-sm mb-3">
                                    <p><span class="text-gray-600">Equipo:</span> {{ $homologacion->robot->equipo->nombre }}</p>
                                    <p><span class="text-gray-600">Tipo:</span> {{ $homologacion->robot->tipo }}</p>
                                    <p><span class="text-gray-600">Medidas:</span> {{ $homologacion->robot->alto }}x{{ $homologacion->robot->ancho }}x{{ $homologacion->robot->largo }} cm</p>
                                    <p><span class="text-gray-600">Peso:</span> {{ $homologacion->robot->peso }} kg</p>
                                </div>
                                
                                @if($homologacion->estado == 'pendiente')
                                    <div class="grid grid-cols-2 gap-2">
                                        <form action="{{ route('homologaciones.update', $homologacion->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="estado" value="aprobado">
                                            <button type="submit" class="w-full py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                                Aprobar
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('homologaciones.update', $homologacion->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="estado" value="rechazado">
                                            <button type="submit" class="w-full py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                                Rechazar
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <form action="{{ route('homologaciones.update', $homologacion->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="estado" value="pendiente">
                                        <button type="submit" class="w-full py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                                            Revertir a Pendiente
                                        </button>
                                    </form>
                                @endif
                                
                                @if($homologacion->observaciones)
                                    <div class="mt-3 p-2 bg-gray-50 rounded text-sm">
                                        <span class="font-medium">Observaciones:</span>
                                        <p>{{ $homologacion->observaciones }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    function filtrarHomologaciones(estado) {
        const cards = document.querySelectorAll('.homologacion-card');
        
        cards.forEach(card => {
            if (estado === 'todos' || card.dataset.estado === estado) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection 