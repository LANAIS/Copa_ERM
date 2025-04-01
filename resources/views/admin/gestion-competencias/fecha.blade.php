@extends('layouts.app')

@section('content')
<div class="container py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $fecha->nombre }}</h1>
            <p class="text-gray-600">{{ $fecha->lugar }} | {{ $fecha->fecha_inicio->format('d/m/Y') }}</p>
            <p class="text-gray-600">
                <a href="{{ route('admin.gestion-competencias.evento', $fecha->evento->id) }}" class="text-blue-600 hover:underline">
                    {{ $fecha->evento->nombre }}
                </a>
            </p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.gestion-competencias.evento', $fecha->evento->id) }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
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
    
    <!-- Información de la fecha -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
        <div class="lg:col-span-1">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="bg-blue-600 text-white px-4 py-3">
                    <h2 class="text-lg font-medium">Información</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div>
                        <span class="text-gray-600 block">Descripción:</span>
                        <p>{{ $fecha->descripcion ?? 'Sin descripción' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-600 block">Fecha inicio:</span>
                        <p>{{ $fecha->fecha_inicio->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-600 block">Fecha fin:</span>
                        <p>{{ $fecha->fecha_fin->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-600 block">Orden:</span>
                        <p>{{ $fecha->orden }}</p>
                    </div>
                    <div>
                        <span class="text-gray-600 block">Estado:</span>
                        <span class="inline-block px-2 py-1 rounded text-sm {{ $fecha->activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $fecha->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="lg:col-span-3">
            <div class="bg-white shadow-md rounded-lg overflow-hidden h-full">
                <div class="bg-blue-600 text-white px-4 py-3">
                    <h2 class="text-lg font-medium">Estado de las Competencias</h2>
                </div>
                <div class="p-4">
                    @php
                        $estadosCompetencia = [
                            'creada' => 0,
                            'inscripciones' => 0,
                            'homologacion' => 0,
                            'armado_llaves' => 0, 
                            'en_curso' => 0,
                            'finalizada' => 0
                        ];
                        
                        foreach ($fecha->evento->categorias as $categoria) {
                            if (isset($estadosCompetencia[$categoria->estado_competencia])) {
                                $estadosCompetencia[$categoria->estado_competencia]++;
                            }
                        }
                        
                        $totalCategorias = array_sum($estadosCompetencia);
                    @endphp
                    
                    <div class="space-y-4">
                        <!-- Barra de progreso general -->
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>Progreso General</span>
                                <span>{{ $estadosCompetencia['finalizada'] }}/{{ $totalCategorias }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-5">
                                <div class="flex h-5 rounded-full overflow-hidden">
                                    <div class="bg-gray-400 h-5" style="width: {{ $totalCategorias > 0 ? ($estadosCompetencia['creada'] / $totalCategorias) * 100 : 0 }}%"></div>
                                    <div class="bg-blue-400 h-5" style="width: {{ $totalCategorias > 0 ? ($estadosCompetencia['inscripciones'] / $totalCategorias) * 100 : 0 }}%"></div>
                                    <div class="bg-yellow-400 h-5" style="width: {{ $totalCategorias > 0 ? ($estadosCompetencia['homologacion'] / $totalCategorias) * 100 : 0 }}%"></div>
                                    <div class="bg-indigo-400 h-5" style="width: {{ $totalCategorias > 0 ? ($estadosCompetencia['armado_llaves'] / $totalCategorias) * 100 : 0 }}%"></div>
                                    <div class="bg-green-400 h-5" style="width: {{ $totalCategorias > 0 ? ($estadosCompetencia['en_curso'] / $totalCategorias) * 100 : 0 }}%"></div>
                                    <div class="bg-purple-400 h-5" style="width: {{ $totalCategorias > 0 ? ($estadosCompetencia['finalizada'] / $totalCategorias) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="flex flex-wrap text-xs mt-1">
                                <span class="mr-2 text-gray-600">■ Creada ({{ $estadosCompetencia['creada'] }})</span>
                                <span class="mr-2 text-blue-600">■ Inscripciones ({{ $estadosCompetencia['inscripciones'] }})</span>
                                <span class="mr-2 text-yellow-600">■ Homologación ({{ $estadosCompetencia['homologacion'] }})</span>
                                <span class="mr-2 text-indigo-600">■ Armado ({{ $estadosCompetencia['armado_llaves'] }})</span>
                                <span class="mr-2 text-green-600">■ En Curso ({{ $estadosCompetencia['en_curso'] }})</span>
                                <span class="mr-2 text-purple-600">■ Finalizada ({{ $estadosCompetencia['finalizada'] }})</span>
                            </div>
                        </div>
                        
                        <!-- Contadores individuales -->
                        <div class="grid grid-cols-3 md:grid-cols-6 gap-2 mt-4">
                            <div class="p-2 bg-gray-100 rounded text-center">
                                <span class="block text-lg font-bold">{{ $estadosCompetencia['creada'] }}</span>
                                <span class="block text-xs text-gray-600">Creadas</span>
                            </div>
                            <div class="p-2 bg-blue-100 rounded text-center">
                                <span class="block text-lg font-bold">{{ $estadosCompetencia['inscripciones'] }}</span>
                                <span class="block text-xs text-gray-600">Inscripciones</span>
                            </div>
                            <div class="p-2 bg-yellow-100 rounded text-center">
                                <span class="block text-lg font-bold">{{ $estadosCompetencia['homologacion'] }}</span>
                                <span class="block text-xs text-gray-600">Homologación</span>
                            </div>
                            <div class="p-2 bg-indigo-100 rounded text-center">
                                <span class="block text-lg font-bold">{{ $estadosCompetencia['armado_llaves'] }}</span>
                                <span class="block text-xs text-gray-600">Armado</span>
                            </div>
                            <div class="p-2 bg-green-100 rounded text-center">
                                <span class="block text-lg font-bold">{{ $estadosCompetencia['en_curso'] }}</span>
                                <span class="block text-xs text-gray-600">En Curso</span>
                            </div>
                            <div class="p-2 bg-purple-100 rounded text-center">
                                <span class="block text-lg font-bold">{{ $estadosCompetencia['finalizada'] }}</span>
                                <span class="block text-xs text-gray-600">Finalizadas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Categorías en esta fecha -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-blue-600 text-white px-4 py-3">
            <h2 class="text-lg font-medium">Categorías en esta Fecha</h2>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($fecha->evento->categorias as $categoriaEvento)
                    <div class="border rounded-lg overflow-hidden bg-white hover:shadow-md transition-shadow">
                        <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
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
                        <div class="p-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Inscritos:</span>
                                <span>{{ $categoriaEvento->inscritos }} / {{ $categoriaEvento->cupo_limite ?: '∞' }}</span>
                            </div>
                            
                            @if($categoriaEvento->estado_competencia != 'creada')
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Inscripciones:</span>
                                    <span>{{ $categoriaEvento->inscripciones_abiertas ? 'Abiertas' : 'Cerradas' }}</span>
                                </div>
                            @endif
                            
                            <div class="mt-4">
                                <a href="{{ route('admin.gestion-competencias.categoria', $categoriaEvento->id) }}" 
                                   class="block w-full text-center py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Gestionar
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="lg:col-span-3 text-center py-8">
                        <p class="text-gray-500">No hay categorías configuradas para esta fecha</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection 