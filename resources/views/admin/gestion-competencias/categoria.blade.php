@extends('layouts.app')

@section('content')
<div class="container py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $categoria->categoria->nombre }}</h1>
            <p class="text-gray-600">{{ $categoria->evento->nombre }} | {{ $categoria->evento->lugar }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.gestion-competencias.evento', $categoria->evento->id) }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                Volver al Evento
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
    
    <!-- Resumen de estado -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="bg-blue-600 text-white px-4 py-3">
            <h2 class="text-lg font-medium">Estado de la Competencia</h2>
        </div>
        <div class="p-4">
            <!-- Fases de la competencia -->
            <div class="flex flex-wrap mb-4">
                @php
                    $estados = [
                        'creada' => ['label' => 'Creada', 'color' => 'bg-gray-200'],
                        'inscripciones' => ['label' => 'Inscripciones', 'color' => 'bg-blue-200'],
                        'homologacion' => ['label' => 'Homologación', 'color' => 'bg-yellow-200'],
                        'armado_llaves' => ['label' => 'Armado de Llaves', 'color' => 'bg-indigo-200'],
                        'en_curso' => ['label' => 'En Curso', 'color' => 'bg-green-200'],
                        'finalizada' => ['label' => 'Finalizada', 'color' => 'bg-purple-200']
                    ];
                    
                    $estadoActual = $categoria->estado_competencia;
                    $estadoIndex = array_search($estadoActual, array_keys($estados));
                @endphp
                
                <div class="w-full flex items-center">
                    @foreach($estados as $key => $estado)
                        @php
                            $keyIndex = array_search($key, array_keys($estados));
                            $active = $keyIndex <= $estadoIndex;
                            $current = $key === $estadoActual;
                        @endphp
                        
                        <div class="flex-1 relative">
                            <div class="mx-2 p-2 text-center rounded-lg {{ $active ? $estado['color'] : 'bg-gray-100' }} 
                                        {{ $current ? 'ring-2 ring-blue-500' : '' }}">
                                <span class="text-sm font-medium {{ $active ? 'text-gray-800' : 'text-gray-500' }}">
                                    {{ $estado['label'] }}
                                </span>
                            </div>
                            
                            @if($keyIndex < count($estados) - 1)
                                <div class="absolute top-1/2 right-0 h-0.5 w-4 {{ $active && $keyIndex < $estadoIndex ? $estados[array_keys($estados)[$keyIndex+1]]['color'] : 'bg-gray-100' }} transform translate-x-1/2 -translate-y-1/2"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Estadísticas y acciones según la fase -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="p-4 border rounded-lg bg-gray-50">
                        <h3 class="font-semibold mb-2">Resumen</h3>
                        <ul class="space-y-2">
                            <li class="flex justify-between">
                                <span class="text-gray-600">Inscritos:</span>
                                <span>{{ $categoria->inscritos }} / {{ $categoria->cupo_limite ?: '∞' }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-600">Equipos:</span>
                                <span>{{ $categoria->inscripciones->count() }}</span>
                            </li>
                            @if($categoria->estado_competencia == 'homologacion' || $estadoIndex > array_search('homologacion', array_keys($estados)))
                                <li class="flex justify-between">
                                    <span class="text-gray-600">Homologaciones:</span>
                                    <span>{{ $statsHomologacion['aprobadas'] }} / {{ $statsHomologacion['total'] }}</span>
                                </li>
                            @endif
                            @if($categoria->llave)
                                <li class="flex justify-between">
                                    <span class="text-gray-600">Tipo de Fixture:</span>
                                    <span>
                                        @switch($categoria->llave->tipo_fixture)
                                            @case('eliminacion_directa')
                                                Eliminación Directa
                                                @break
                                            @case('todos_contra_todos')
                                                Todos contra Todos
                                                @break
                                            @case('suizo')
                                                Sistema Suizo
                                                @break
                                            @default
                                                {{ $categoria->llave->tipo_fixture }}
                                        @endswitch
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
                
                <div>
                    <div class="p-4 border rounded-lg bg-gray-50">
                        <h3 class="font-semibold mb-2">Acciones</h3>
                        
                        @switch($estadoActual)
                            @case('creada')
                                <form action="{{ route('admin.gestion-competencias.cambiar-estado', $categoria->id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <input type="hidden" name="estado" value="inscripciones">
                                    <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                        Iniciar Inscripciones
                                    </button>
                                </form>
                                <p class="text-sm text-gray-600">Inicia el período de inscripciones para esta categoría.</p>
                                @break
                                
                            @case('inscripciones')
                                <form action="{{ route('admin.gestion-competencias.cambiar-estado', $categoria->id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <input type="hidden" name="estado" value="homologacion">
                                    <button type="submit" class="w-full py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                                        Iniciar Homologación
                                    </button>
                                </form>
                                <p class="text-sm text-gray-600">Cierra las inscripciones y comienza la fase de homologación de robots.</p>
                                @break
                                
                            @case('homologacion')
                                <a href="{{ route('admin.gestion-competencias.homologaciones', $categoria->id) }}" 
                                   class="block w-full py-2 text-center bg-yellow-600 text-white rounded hover:bg-yellow-700 mb-2">
                                    Gestionar Homologaciones
                                </a>
                                <form action="{{ route('admin.gestion-competencias.cambiar-estado', $categoria->id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <input type="hidden" name="estado" value="armado_llaves">
                                    <button type="submit" class="w-full py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700" 
                                            {{ $statsHomologacion['pendientes'] > 0 ? 'disabled' : '' }}>
                                        Iniciar Armado de Llaves
                                    </button>
                                </form>
                                @if($statsHomologacion['pendientes'] > 0)
                                    <p class="text-sm text-red-600">Hay {{ $statsHomologacion['pendientes'] }} homologaciones pendientes.</p>
                                @else
                                    <p class="text-sm text-gray-600">Todas las homologaciones están completas. Puedes avanzar a la siguiente fase.</p>
                                @endif
                                @break
                                
                            @case('armado_llaves')
                                @if($categoria->llave)
                                    <a href="{{ route('admin.brackets.admin', $categoria->llave->id) }}" 
                                       class="block w-full py-2 text-center bg-indigo-600 text-white rounded hover:bg-indigo-700 mb-2">
                                        Administrar Llave
                                    </a>
                                    <form action="{{ route('admin.gestion-competencias.cambiar-estado', $categoria->id) }}" method="POST" class="mb-2">
                                        @csrf
                                        <input type="hidden" name="estado" value="en_curso">
                                        <button type="submit" class="w-full py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                            Iniciar Competencia
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.gestion-competencias.crear-llave', $categoria->id) }}" method="POST" class="mb-2">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Fixture</label>
                                            <select name="tipo_fixture" class="w-full rounded-md border-gray-300 shadow-sm">
                                                <option value="eliminacion_directa">Eliminación Directa</option>
                                                <option value="todos_contra_todos">Todos contra Todos</option>
                                                <option value="suizo">Sistema Suizo</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="w-full py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                            Crear Llave
                                        </button>
                                    </form>
                                @endif
                                @break
                                
                            @case('en_curso')
                                <a href="{{ route('admin.brackets.admin', $categoria->llave->id) }}" 
                                   class="block w-full py-2 text-center bg-green-600 text-white rounded hover:bg-green-700 mb-2">
                                    Gestionar Competencia
                                </a>
                                <a href="{{ route('brackets.show', $categoria->llave->id) }}" 
                                   class="block w-full py-2 text-center bg-blue-600 text-white rounded hover:bg-blue-700 mb-2">
                                    Ver Bracket
                                </a>
                                <form action="{{ route('admin.gestion-competencias.cambiar-estado', $categoria->id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <input type="hidden" name="estado" value="finalizada">
                                    <button type="submit" class="w-full py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                                        Finalizar Competencia
                                    </button>
                                </form>
                                @break
                                
                            @case('finalizada')
                                <a href="{{ route('brackets.show', $categoria->llave->id) }}" 
                                   class="block w-full py-2 text-center bg-blue-600 text-white rounded hover:bg-blue-700 mb-2">
                                    Ver Resultados
                                </a>
                                @break
                                
                            @default
                                <p class="text-sm text-gray-600">Estado desconocido.</p>
                        @endswitch
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contenido específico según la fase -->
    <div class="grid grid-cols-1 gap-6">
        <!-- Panel de inscripciones -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-blue-600 text-white px-4 py-3">
                <h2 class="text-lg font-medium">Equipos Inscritos</h2>
            </div>
            <div class="p-4">
                @if($categoria->inscripciones->isEmpty())
                    <div class="text-center py-4">
                        <p class="text-gray-500">No hay equipos inscritos en esta categoría</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Robots</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    @if($categoria->estado_competencia == 'homologacion')
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Homologación</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($categoria->inscripciones as $inscripcion)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $inscripcion->equipo->nombre }}</div>
                                            <div class="text-sm text-gray-500">{{ $inscripcion->equipo->institucion }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                @if($inscripcion->robotsParticipantes->isEmpty())
                                                    <span class="text-red-500">Sin robots</span>
                                                @else
                                                    <ul class="list-disc list-inside">
                                                        @foreach($inscripcion->robotsParticipantes as $robot)
                                                            <li>{{ $robot->nombre }}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $inscripcion->estado == 'pendiente' ? 'bg-yellow-100 text-yellow-800' : 
                                                       ($inscripcion->estado == 'aceptada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($inscripcion->estado) }}
                                            </span>
                                        </td>
                                        @if($categoria->estado_competencia == 'homologacion')
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $robotsHomologados = 0;
                                                    $totalRobots = $inscripcion->robotsParticipantes->count();
                                                    
                                                    foreach($inscripcion->robotsParticipantes as $robot) {
                                                        $homologacion = $categoria->homologaciones->where('robot_id', $robot->id)->first();
                                                        if($homologacion && $homologacion->estado === 'aprobado') {
                                                            $robotsHomologados++;
                                                        }
                                                    }
                                                @endphp
                                                
                                                <div class="flex items-center">
                                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $totalRobots > 0 ? ($robotsHomologados / $totalRobots) * 100 : 0 }}%"></div>
                                                    </div>
                                                    <span class="ml-2 text-sm text-gray-600">{{ $robotsHomologados }}/{{ $totalRobots }}</span>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 