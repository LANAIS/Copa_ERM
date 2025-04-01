@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6 px-4">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Bracket: {{ $llave->categoriaEvento->categoria->nombre }} - {{ $llave->categoriaEvento->evento->nombre }}
        </h1>
        <p class="text-gray-600">
            Estado: 
            <span class="font-semibold 
                @if($llave->estado_torneo == 'pendiente') text-yellow-600
                @elseif($llave->estado_torneo == 'en_curso') text-green-600
                @else text-blue-600
                @endif">
                {{ ucfirst($llave->estado_torneo) }}
            </span>
        </p>
    </div>

    <div class="flex justify-end mb-4">
        <a href="{{ url()->previous() }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
        
        <a href="{{ route('filament.judge.pages.bracket-admin.{id}', ['id' => $llave->id]) }}" class="ml-3 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            <i class="fas fa-cog mr-2"></i> Administrar
        </a>
    </div>

    <!-- Visualización del bracket -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <!-- Aquí iría la visualización gráfica del bracket. 
             Si tienes una implementación externa, puedes cargarla aquí -->
        <div id="bracket-container" class="min-h-[600px] flex items-center justify-center">
            <p class="text-center text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2"></i> Cargando bracket...
            </p>
        </div>
    </div>

    <!-- Listado de enfrentamientos -->
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-xl font-bold mb-4">Enfrentamientos</h2>
        
        <!-- Filtros -->
        <div class="mb-4">
            <form action="{{ route('judge.brackets.public', $llave->id) }}" method="GET" class="flex flex-wrap gap-3">
                <div>
                    <label for="ronda_filter" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por ronda</label>
                    <select name="ronda_filter" id="ronda_filter" class="border border-gray-300 rounded px-3 py-2">
                        <option value="">Todas las rondas</option>
                        @foreach($llave->enfrentamientos->pluck('ronda')->unique()->sort() as $ronda)
                            <option value="{{ $ronda }}" {{ request('ronda_filter') == $ronda ? 'selected' : '' }}>
                                @if($ronda == $llave->estructura['total_rondas'])
                                    Final
                                @elseif($ronda == $llave->estructura['total_rondas'] - 1)
                                    Semifinal
                                @elseif($ronda == $llave->estructura['total_rondas'] - 2)
                                    Cuartos de final
                                @else
                                    Ronda {{ $ronda }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="estado_filter" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por estado</label>
                    <select name="estado_filter" id="estado_filter" class="border border-gray-300 rounded px-3 py-2">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" {{ request('estado_filter') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="completado" {{ request('estado_filter') == 'completado' ? 'selected' : '' }}>Completado</option>
                    </select>
                </div>
                
                <div class="self-end">
                    <button type="submit" class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        <i class="fas fa-filter mr-2"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Tabla de enfrentamientos -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ronda</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posición</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipo 1</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipo 2</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puntuación</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ganador</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php
                        $enfrentamientos = $llave->enfrentamientos;
                        if (request('ronda_filter')) {
                            $enfrentamientos = $enfrentamientos->where('ronda', request('ronda_filter'));
                        }
                        if (request('estado_filter')) {
                            $enfrentamientos = $enfrentamientos->filter(function($enfrentamiento) {
                                $estado = request('estado_filter');
                                if ($estado === 'completado') {
                                    return $enfrentamiento->tieneResultado();
                                } elseif ($estado === 'pendiente') {
                                    return !$enfrentamiento->tieneResultado();
                                }
                                return true;
                            });
                        }
                        $enfrentamientos = $enfrentamientos->sortBy('ronda')->sortBy('posicion');
                    @endphp
                    
                    @forelse($enfrentamientos as $enfrentamiento)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($enfrentamiento->ronda == $llave->estructura['total_rondas'])
                                    <span class="font-semibold text-blue-600">Final</span>
                                @elseif($enfrentamiento->ronda == $llave->estructura['total_rondas'] - 1)
                                    <span class="font-semibold text-blue-600">Semifinal</span>
                                @elseif($enfrentamiento->ronda == $llave->estructura['total_rondas'] - 2)
                                    <span class="font-semibold text-blue-600">Cuartos de final</span>
                                @else
                                    Ronda {{ $enfrentamiento->ronda }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $enfrentamiento->posicion }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($enfrentamiento->equipo1)
                                    <span class="{{ $enfrentamiento->ganador_id == $enfrentamiento->equipo1_id ? 'font-semibold' : '' }}">
                                        {{ $enfrentamiento->equipo1->nombre }}
                                    </span>
                                @else
                                    <span class="text-gray-500">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($enfrentamiento->equipo2)
                                    <span class="{{ $enfrentamiento->ganador_id == $enfrentamiento->equipo2_id ? 'font-semibold' : '' }}">
                                        {{ $enfrentamiento->equipo2->nombre }}
                                    </span>
                                @else
                                    <span class="text-gray-500">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($enfrentamiento->tieneResultado())
                                    {{ $enfrentamiento->puntaje_equipo1 }} - {{ $enfrentamiento->puntaje_equipo2 }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($enfrentamiento->tieneResultado())
                                    <span class="font-semibold text-green-600">
                                        {{ $enfrentamiento->ganador->nombre }}
                                    </span>
                                @else
                                    <span class="text-gray-500">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($enfrentamiento->tieneResultado())
                                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                                        Completado
                                    </span>
                                @elseif($enfrentamiento->enCurso())
                                    <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">
                                        En curso
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded-full">
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No hay enfrentamientos disponibles.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Aquí puedes añadir JavaScript para cargar la visualización del bracket
    // Por ejemplo, usando una librería de visualización de brackets
    document.addEventListener('DOMContentLoaded', function() {
        // Simulación de carga del bracket
        setTimeout(function() {
            document.getElementById('bracket-container').innerHTML = 
                '<p class="text-center text-gray-500">Visualización del bracket disponible pronto.</p>';
        }, 1000);
    });
</script>
@endpush
@endsection 