@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ isset($editando) && $editando ? 'Editar' : 'Registrar' }} Homologación</h1>
        
        <a href="{{ route('homologaciones.index', $categoriaEvento->id) }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
            Volver
        </a>
    </div>
    
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Detalles del Robot
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Información del robot a homologar
            </p>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $robot->nombre }}</dd>
                </div>
                
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Categoría</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $categoriaEvento->categoria->nombre }}</dd>
                </div>
                
                @if($robot->equipo)
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Equipo</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $robot->equipo->nombre }}</dd>
                    </div>
                @endif
                
                @if($robot->modalidad)
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Modalidad</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $robot->modalidad }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>
    
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-6">
            <h2 class="text-lg font-medium mb-4">Formulario de Homologación</h2>
            
            @if(isset($editando) && $editando)
                <form action="{{ route('homologaciones.update', $homologacion->id) }}" method="POST" class="space-y-4">
                @method('PUT')
            @else
                <form action="{{ route('homologaciones.store') }}" method="POST" class="space-y-4">
            @endif
                @csrf
                
                <input type="hidden" name="robot_id" value="{{ $robot->id }}">
                <input type="hidden" name="categoria_evento_id" value="{{ $categoriaEvento->id }}">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="peso" class="block text-sm font-medium text-gray-700 mb-1">Peso (g)</label>
                        <input type="number" id="peso" name="peso" step="0.01" min="0" 
                               value="{{ old('peso', isset($homologacion) ? $homologacion->peso : '') }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('peso')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="ancho" class="block text-sm font-medium text-gray-700 mb-1">Ancho (cm)</label>
                        <input type="number" id="ancho" name="ancho" step="0.1" min="0" 
                               value="{{ old('ancho', isset($homologacion) ? $homologacion->ancho : '') }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('ancho')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="largo" class="block text-sm font-medium text-gray-700 mb-1">Largo (cm)</label>
                        <input type="number" id="largo" name="largo" step="0.1" min="0" 
                               value="{{ old('largo', isset($homologacion) ? $homologacion->largo : '') }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('largo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="alto" class="block text-sm font-medium text-gray-700 mb-1">Alto (cm)</label>
                        <input type="number" id="alto" name="alto" step="0.1" min="0" 
                               value="{{ old('alto', isset($homologacion) ? $homologacion->alto : '') }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('alto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-6">
                    <label for="resultado" class="block text-sm font-medium text-gray-700 mb-2">Resultado</label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="resultado" value="aprobado" 
                                   {{ old('resultado', isset($homologacion) ? $homologacion->resultado : 'aprobado') === 'aprobado' ? 'checked' : '' }}
                                   class="form-radio h-4 w-4 text-blue-600">
                            <span class="ml-2 text-gray-700">Aprobado</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="resultado" value="rechazado" 
                                   {{ old('resultado', isset($homologacion) ? $homologacion->resultado : '') === 'rechazado' ? 'checked' : '' }}
                                   class="form-radio h-4 w-4 text-red-600">
                            <span class="ml-2 text-gray-700">Rechazado</span>
                        </label>
                    </div>
                    @error('resultado')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mt-4">
                    <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="3" 
                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('observaciones', isset($homologacion) ? $homologacion->observaciones : '') }}</textarea>
                    @error('observaciones')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex justify-end mt-6">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        {{ isset($editando) && $editando ? 'Actualizar Homologación' : 'Registrar Homologación' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 