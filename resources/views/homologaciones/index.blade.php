@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Homologaciones - {{ $categoriaEvento->categoria->nombre }}</h1>
        
        <div class="flex space-x-2">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                Volver
            </a>
            
            @if($categoriaEvento->estado_competencia === 'homologacion')
                <form action="{{ route('homologaciones.finalizar', $categoriaEvento->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Finalizar Homologaciones
                    </button>
                </form>
            @endif
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
    
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Detalles de la Competencia
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Información general sobre la categoría y el evento
            </p>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Categoría</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $categoriaEvento->categoria->nombre }}</dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Evento</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $categoriaEvento->evento->nombre }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Estado</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <span class="px-2 py-1 rounded-full text-sm 
                            @if($categoriaEvento->estado_competencia == 'creada') bg-gray-200 text-gray-800
                            @elseif($categoriaEvento->estado_competencia == 'homologacion') bg-yellow-200 text-yellow-800
                            @elseif($categoriaEvento->estado_competencia == 'armado_llaves') bg-blue-200 text-blue-800
                            @elseif($categoriaEvento->estado_competencia == 'en_curso') bg-green-200 text-green-800
                            @elseif($categoriaEvento->estado_competencia == 'finalizada') bg-red-200 text-red-800
                            @endif">
                            {{ ucfirst($categoriaEvento->estado_competencia) }}
                        </span>
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Inscritos</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $inscripciones->count() }}</dd>
                </div>
            </dl>
        </div>
    </div>
    
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4">
            <h2 class="text-lg font-medium mb-4">Robots Inscritos</h2>
            
            @if($inscripciones->isEmpty())
                <div class="bg-yellow-50 border border-yellow-400 text-yellow-700 p-4 rounded">
                    No hay equipos inscritos en esta categoría.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipo</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Robot</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dimensiones</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($inscripciones as $inscripcion)
                                @if($inscripcion->robots && $inscripcion->robots->count() > 0)
                                    @foreach($inscripcion->robots as $robot)
                                        <tr>
                                            <td class="py-4 px-4 text-sm">{{ $inscripcion->equipo->nombre }}</td>
                                            <td class="py-4 px-4 text-sm">{{ $robot->nombre }}</td>
                                            <td class="py-4 px-4 text-sm">
                                                @php
                                                $homologacion = $robot->obtenerHomologacion($categoriaEvento->id);
                                                @endphp
                                                
                                                @if(!$homologacion)
                                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                                @elseif($homologacion->resultado === 'aprobado')
                                                    <span class="badge bg-success text-white">Aprobado</span>
                                                @else
                                                    <span class="badge bg-danger text-white">Rechazado</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($homologacion)
                                                    Peso: {{ number_format($homologacion->peso, 0) }}g | 
                                                    {{ number_format($homologacion->ancho, 1) }} x 
                                                    {{ number_format($homologacion->largo, 1) }} x 
                                                    {{ number_format($homologacion->alto, 1) }} cm
                                                @else
                                                    <span class="text-muted">No registrado</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($categoriaEvento->estado_competencia == 'homologacion')
                                                    @if($homologacion)
                                                        <a href="{{ route('homologaciones.edit', ['robot' => $robot->id, 'categoriaEvento' => $categoriaEvento->id]) }}" 
                                                           class="btn btn-warning btn-sm">
                                                            <i class="fas fa-edit"></i> Editar
                                                        </a>
                                                    @else
                                                        <a href="{{ route('homologaciones.create', ['robot' => $robot->id, 'categoriaEvento' => $categoriaEvento->id]) }}" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="fas fa-clipboard-check"></i> Homologar
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No disponible</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="py-4 px-4 text-sm">{{ $inscripcion->equipo->nombre }}</td>
                                        <td colspan="4" class="py-4 px-4 text-sm text-gray-500 italic">No hay robots registrados</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 