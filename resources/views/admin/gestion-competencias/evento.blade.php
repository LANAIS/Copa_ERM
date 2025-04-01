@extends('layouts.app')

@section('content')
<div class="container py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $evento->nombre }}</h1>
            <p class="text-gray-600">{{ $evento->lugar }} | {{ $evento->fecha_inicio->format('d/m/Y') }} - {{ $evento->fecha_fin->format('d/m/Y') }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.gestion-competencias.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                Volver
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
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Panel izquierdo: Información del evento -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                <div class="bg-blue-600 text-white px-4 py-3">
                    <h2 class="text-lg font-medium">Información</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div>
                        <span class="text-gray-600 block">Descripción:</span>
                        <p>{{ $evento->descripcion ?? 'Sin descripción' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-600 block">Fecha inicio:</span>
                        <p>{{ $evento->fecha_inicio->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-600 block">Fecha fin:</span>
                        <p>{{ $evento->fecha_fin->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-600 block">Inscripciones:</span>
                        <p>{{ $evento->inicio_inscripciones->format('d/m/Y') }} - {{ $evento->fin_inscripciones->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-600 block">Estado:</span>
                        <span class="inline-block px-2 py-1 rounded text-sm
                            @if($evento->estado == 'abierto') bg-green-100 text-green-800
                            @elseif($evento->estado == 'creado') bg-gray-100 text-gray-800 
                            @elseif($evento->estado == 'finalizado') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($evento->estado) }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-600 block">Publicado:</span>
                        <span class="inline-block px-2 py-1 rounded text-sm {{ $evento->publicado ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $evento->publicado ? 'Sí' : 'No' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Panel central y derecho: Fechas y Categorías -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                <div class="bg-blue-600 text-white px-4 py-3 flex justify-between items-center">
                    <h2 class="text-lg font-medium">Fechas del Evento</h2>
                </div>
                <div class="p-4">
                    @if($evento->fechas->isEmpty())
                        <div class="text-center py-4">
                            <p class="text-gray-500">No hay fechas configuradas para este evento</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($evento->fechas->sortBy('orden') as $fecha)
                                <div class="border rounded-lg overflow-hidden bg-white hover:shadow-md transition-shadow">
                                    <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
                                        <div>
                                            <h3 class="font-bold">{{ $fecha->nombre }}</h3>
                                            <p class="text-sm text-gray-600">{{ $fecha->lugar }} | {{ $fecha->fecha_inicio->format('d/m/Y') }}</p>
                                        </div>
                                        <a href="{{ route('admin.gestion-competencias.fecha', $fecha->id) }}" 
                                           class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                            Gestionar
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Categorías del evento -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="bg-blue-600 text-white px-4 py-3 flex justify-between items-center">
                    <h2 class="text-lg font-medium">Categorías del Evento</h2>
                </div>
                <div class="p-4">
                    @if($evento->categorias->isEmpty())
                        <div class="text-center py-4">
                            <p class="text-gray-500">No hay categorías configuradas para este evento</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($evento->categorias->sortBy('categoria.nombre') as $categoriaEvento)
                                <div class="border rounded-lg overflow-hidden bg-white hover:shadow-md transition-shadow">
                                    <div class="p-3 border-b bg-gray-50 flex justify-between items-center">
                                        <h3 class="font-bold">{{ $categoriaEvento->categoria->nombre }}</h3>
                                        
                                        <!-- Mostrar el estado de la competencia -->
                                        <span class="inline-block px-2 py-1 rounded text-xs
                                            @if($categoriaEvento->estado_competencia == 'creada') bg-gray-100 text-gray-800
                                            @elseif($categoriaEvento->estado_competencia == 'inscripciones') bg-blue-100 text-blue-800
                                            @elseif($categoriaEvento->estado_competencia == 'homologacion') bg-yellow-100 text-yellow-800
                                            @elseif($categoriaEvento->estado_competencia == 'armado_llaves') bg-indigo-100 text-indigo-800
                                            @elseif($categoriaEvento->estado_competencia == 'en_curso') bg-green-100 text-green-800
                                            @elseif($categoriaEvento->estado_competencia == 'finalizada') bg-purple-100 text-purple-800
                                            @endif">
                                            @php
                                                $estadosTexto = [
                                                    'creada' => 'Creada',
                                                    'inscripciones' => 'Inscripciones',
                                                    'homologacion' => 'Homologación',
                                                    'armado_llaves' => 'Armado de Llaves',
                                                    'en_curso' => 'En Curso',
                                                    'finalizada' => 'Finalizada'
                                                ];
                                            @endphp
                                            {{ $estadosTexto[$categoriaEvento->estado_competencia] ?? 'Desconocido' }}
                                        </span>
                                    </div>
                                    <div class="p-3 space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Inscritos:</span>
                                            <span>{{ $categoriaEvento->inscritos }} / {{ $categoriaEvento->cupo_limite ?: '∞' }}</span>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <a href="{{ route('admin.gestion-competencias.categoria', $categoriaEvento->id) }}" 
                                               class="block w-full text-center py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                                Gestionar Competencia
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
    </div>
</div>
@endsection 