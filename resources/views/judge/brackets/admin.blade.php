@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6 px-4">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Administración de Bracket: {{ $bracket->categoriaEvento->categoria->nombre }} - {{ $bracket->categoriaEvento->evento->nombre }}
        </h1>
        <p class="text-gray-600">
            Estado: 
            <span class="font-semibold 
                @if($bracket->estado_torneo == 'pendiente') text-yellow-600
                @elseif($bracket->estado_torneo == 'en_curso') text-green-600
                @else text-blue-600
                @endif">
                {{ ucfirst($bracket->estado_torneo) }}
            </span>
        </p>
    </div>

    <div class="flex justify-between mb-4">
        <!-- Acciones -->
        <div class="flex space-x-4">
            <a href="{{ route('judge.brackets.public', $bracket->id) }}" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                <i class="fas fa-eye mr-2"></i> Ver Bracket Público
            </a>
            
            <form action="{{ route('judge.brackets.cambiar-estado', $bracket->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded hover:opacity-90
                    @if($bracket->estado_torneo == 'pendiente') bg-green-500 text-white
                    @elseif($bracket->estado_torneo == 'en_curso') bg-red-500 text-white
                    @else bg-yellow-500 text-white
                    @endif"
                    onclick="return confirm('{{ 
                        $bracket->estado_torneo == 'pendiente' ? '¿Seguro que desea iniciar el torneo?' :
                        ($bracket->estado_torneo == 'en_curso' ? '¿Seguro que desea finalizar el torneo?' : 
                        '¿Seguro que desea reiniciar el torneo? Se perderán todos los resultados.')
                    }}')">
                    @if($bracket->estado_torneo == 'pendiente')
                        <i class="fas fa-play mr-2"></i> Iniciar Torneo
                    @elseif($bracket->estado_torneo == 'en_curso')
                        <i class="fas fa-stop mr-2"></i> Finalizar Torneo
                    @else
                        <i class="fas fa-redo mr-2"></i> Reiniciar Torneo
                    @endif
                </button>
            </form>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form action="{{ route('judge.brackets.filtrar', $bracket->id) }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar equipos</label>
                <input type="text" name="search" id="search" class="w-full border border-gray-300 rounded px-3 py-2" 
                    value="{{ $search ?? '' }}" placeholder="Nombre del equipo...">
            </div>
            
            <div>
                <label for="estado_filter" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por estado</label>
                <select name="estado_filter" id="estado_filter" class="w-full border border-gray-300 rounded px-3 py-2">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" {{ ($estadoFilter ?? '') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="completado" {{ ($estadoFilter ?? '') == 'completado' ? 'selected' : '' }}>Completado</option>
                </select>
            </div>
            
            <div>
                <label for="ronda_filter" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por ronda</label>
                <select name="ronda_filter" id="ronda_filter" class="w-full border border-gray-300 rounded px-3 py-2">
                    <option value="">Todas las rondas</option>
                    @foreach($rondas as $ronda)
                        <option value="{{ $ronda }}" {{ ($rondaFilter ?? '') == $ronda ? 'selected' : '' }}>
                            @if($ronda == $bracket->estructura['total_rondas'])
                                Final
                            @elseif($ronda == $bracket->estructura['total_rondas'] - 1)
                                Semifinal
                            @elseif($ronda == $bracket->estructura['total_rondas'] - 2)
                                Cuartos de final
                            @else
                                Ronda {{ $ronda }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-filter mr-2"></i> Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Progreso del torneo -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="text-lg font-semibold mb-2">Progreso del torneo</h2>
        <div class="relative pt-1">
            <div class="overflow-hidden h-4 text-xs flex rounded bg-gray-200">
                <div style="width: {{ $porcentajeCompletado }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500">
                    <span class="px-2">{{ $porcentajeCompletado }}%</span>
                </div>
            </div>
            <div class="text-sm text-gray-600 mt-1">
                {{ $completados }} de {{ $totalEnfrentamientos }} enfrentamientos completados
            </div>
        </div>
    </div>

    <!-- Enfrentamientos -->
    @foreach($rondas as $ronda)
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <h2 class="text-xl font-semibold mb-4">
                @if($ronda == $bracket->estructura['total_rondas'])
                    Final
                @elseif($ronda == $bracket->estructura['total_rondas'] - 1)
                    Semifinal
                @elseif($ronda == $bracket->estructura['total_rondas'] - 2)
                    Cuartos de final
                @else
                    Ronda {{ $ronda }}
                @endif
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($enfrentamientos->where('ronda', $ronda) as $enfrentamiento)
                    <div class="border rounded-lg p-4 {{ $enfrentamiento->estado == 'completado' ? 'bg-green-50' : 'bg-gray-50' }}">
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-medium text-gray-500">Enfrentamiento #{{ $enfrentamiento->posicion }}</span>
                            <span class="text-sm font-medium {{ $enfrentamiento->estado == 'completado' ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ ucfirst($enfrentamiento->estado) }}
                            </span>
                        </div>
                        
                        <div class="space-y-3">
                            <!-- Equipo 1 -->
                            <div class="flex items-center {{ $enfrentamiento->ganador_id == $enfrentamiento->equipo1_id ? 'font-bold' : '' }}">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                    <span class="text-blue-800">1</span>
                                </div>
                                <div class="flex-grow">
                                    {{ $enfrentamiento->equipo1 ? $enfrentamiento->equipo1->nombre : 'Pendiente' }}
                                </div>
                                @if($enfrentamiento->estado == 'completado')
                                    <div class="bg-gray-100 px-2 py-1 rounded text-gray-800 font-semibold">
                                        {{ $enfrentamiento->puntuacion_equipo1 }}
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Equipo 2 -->
                            <div class="flex items-center {{ $enfrentamiento->ganador_id == $enfrentamiento->equipo2_id ? 'font-bold' : '' }}">
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-2">
                                    <span class="text-red-800">2</span>
                                </div>
                                <div class="flex-grow">
                                    {{ $enfrentamiento->equipo2 ? $enfrentamiento->equipo2->nombre : 'Pendiente' }}
                                </div>
                                @if($enfrentamiento->estado == 'completado')
                                    <div class="bg-gray-100 px-2 py-1 rounded text-gray-800 font-semibold">
                                        {{ $enfrentamiento->puntuacion_equipo2 }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Acciones -->
                        <div class="mt-4 flex justify-between">
                            @if($bracket->estado_torneo == 'en_curso' && $enfrentamiento->equipo1 && $enfrentamiento->equipo2)
                                @if($enfrentamiento->estado == 'completado')
                                    <form action="{{ route('judge.brackets.reiniciar-resultado', [$bracket->id, $enfrentamiento->id]) }}" method="POST" class="w-full">
                                        @csrf
                                        <button type="submit" class="w-full px-3 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600"
                                                onclick="return confirm('¿Seguro que desea reiniciar este resultado? Se perderán los datos registrados.')">
                                            <i class="fas fa-redo-alt mr-2"></i> Reiniciar Resultado
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="w-full px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                                            onclick="abrirModalResultado('{{ $enfrentamiento->id }}', '{{ $enfrentamiento->equipo1->id }}', '{{ $enfrentamiento->equipo2->id }}', '{{ $enfrentamiento->equipo1->nombre }}', '{{ $enfrentamiento->equipo2->nombre }}')">
                                        <i class="fas fa-trophy mr-2"></i> Registrar Resultado
                                    </button>
                                @endif
                            @elseif($bracket->estado_torneo == 'pendiente')
                                <span class="text-gray-500 text-sm italic">El torneo debe iniciarse para registrar resultados</span>
                            @elseif($bracket->estado_torneo == 'finalizado')
                                <span class="text-gray-500 text-sm italic">El torneo ha finalizado</span>
                            @else
                                <span class="text-gray-500 text-sm italic">Esperando equipos</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <!-- Modal para registrar resultado -->
    <div id="modalResultado" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Registrar Resultado</h3>
                <button type="button" onclick="cerrarModalResultado()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="formResultado" action="" method="POST">
                @csrf
                <div class="mb-4">
                    <p id="equiposEnfrentamiento" class="text-lg font-medium mb-2 text-center"></p>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="puntuacion_equipo1" class="block text-sm font-medium text-gray-700 mb-1">Puntuación Equipo 1</label>
                        <input type="number" name="puntuacion_equipo1" id="puntuacion_equipo1" class="w-full border border-gray-300 rounded px-3 py-2" min="0" required>
                    </div>
                    
                    <div>
                        <label for="puntuacion_equipo2" class="block text-sm font-medium text-gray-700 mb-1">Puntuación Equipo 2</label>
                        <input type="number" name="puntuacion_equipo2" id="puntuacion_equipo2" class="w-full border border-gray-300 rounded px-3 py-2" min="0" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Seleccionar Ganador</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <input type="radio" name="ganador_id" id="ganador_equipo1" class="hidden peer" required>
                            <label for="ganador_equipo1" id="label_equipo1" class="block border border-gray-300 rounded p-3 text-center cursor-pointer peer-checked:bg-blue-50 peer-checked:border-blue-500">
                                Equipo 1
                            </label>
                        </div>
                        
                        <div>
                            <input type="radio" name="ganador_id" id="ganador_equipo2" class="hidden peer">
                            <label for="ganador_equipo2" id="label_equipo2" class="block border border-gray-300 rounded p-3 text-center cursor-pointer peer-checked:bg-blue-50 peer-checked:border-blue-500">
                                Equipo 2
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" onclick="cerrarModalResultado()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded mr-2 hover:bg-gray-300">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Guardar Resultado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
    <div id="alerta-exito" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded shadow-md">
        {{ session('success') }}
    </div>
    <script>
        setTimeout(function() {
            document.getElementById('alerta-exito').style.display = 'none';
        }, 5000);
    </script>
@endif

@if(session('error'))
    <div id="alerta-error" class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded shadow-md">
        {{ session('error') }}
    </div>
    <script>
        setTimeout(function() {
            document.getElementById('alerta-error').style.display = 'none';
        }, 5000);
    </script>
@endif

<script>
    function abrirModalResultado(enfrentamientoId, equipo1Id, equipo2Id, equipo1Nombre, equipo2Nombre) {
        document.getElementById('formResultado').action = "{{ route('judge.brackets.guardar-resultado', [$bracket->id, '']) }}/" + enfrentamientoId;
        document.getElementById('equiposEnfrentamiento').textContent = equipo1Nombre + ' vs ' + equipo2Nombre;
        
        // Configurar IDs y labels
        document.getElementById('ganador_equipo1').value = equipo1Id;
        document.getElementById('ganador_equipo2').value = equipo2Id;
        document.getElementById('label_equipo1').textContent = equipo1Nombre;
        document.getElementById('label_equipo2').textContent = equipo2Nombre;
        
        // Mostrar modal
        document.getElementById('modalResultado').classList.remove('hidden');
    }
    
    function cerrarModalResultado() {
        document.getElementById('modalResultado').classList.add('hidden');
        document.getElementById('formResultado').reset();
    }
    
    // Cerrar modal al hacer clic fuera
    document.getElementById('modalResultado').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalResultado();
        }
    });
</script>
@endsection 