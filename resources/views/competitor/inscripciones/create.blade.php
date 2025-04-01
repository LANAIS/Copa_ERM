@extends('layouts.competitor')

@section('content')
<div>
    <div class="mb-6">
        <a href="{{ route('competitor.inscripciones.index') }}" class="flex items-center text-blue-600 hover:text-blue-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Volver a inscripciones
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Nueva Inscripción</h1>

        @if($errors->any())
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('competitor.inscribirCompetencia') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Selección de competencia -->
                <div>
                    <label for="competition_id" class="block text-sm font-medium text-gray-700 mb-1">Competencia</label>
                    <select name="competition_id" id="competition_id" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('competition_id') border-red-500 @enderror">
                        <option value="">Selecciona una competencia</option>
                        @foreach($competencias as $competencia)
                            <option value="{{ $competencia->id }}" {{ old('competition_id', request('competition_id')) == $competencia->id ? 'selected' : '' }}>
                                {{ $competencia->name }} ({{ $competencia->year }})
                            </option>
                        @endforeach
                    </select>
                    @error('competition_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Información adicional de la competencia (aparece al seleccionar) -->
                <div id="competition-info" class="hidden p-4 bg-blue-50 rounded-md">
                    <p class="text-sm text-blue-800">Selecciona una competencia para ver los detalles</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Selección de robot -->
                <div>
                    <label for="robot_id" class="block text-sm font-medium text-gray-700 mb-1">Robot</label>
                    <select name="robot_id" id="robot_id" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('robot_id') border-red-500 @enderror">
                        <option value="">Selecciona un robot</option>
                        @foreach($robots as $robot)
                            <option value="{{ $robot->id }}" {{ old('robot_id', request('robot_id')) == $robot->id ? 'selected' : '' }}>
                                {{ $robot->nombre }} ({{ ucfirst($robot->categoria) }})
                            </option>
                        @endforeach
                    </select>
                    @error('robot_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    
                    @if($robots->isEmpty())
                        <p class="mt-1 text-sm text-yellow-600">
                            No tienes robots registrados. 
                            <a href="{{ route('competitor.robots.create') }}" class="text-blue-600 hover:text-blue-800 underline">Crea uno aquí</a>
                        </p>
                    @endif
                </div>

                <!-- Selección de equipo -->
                <div>
                    <label for="equipo_id" class="block text-sm font-medium text-gray-700 mb-1">Equipo</label>
                    <select name="equipo_id" id="equipo_id" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('equipo_id') border-red-500 @enderror">
                        <option value="">Selecciona un equipo</option>
                        @foreach($equipos as $equipo)
                            <option value="{{ $equipo->id }}" {{ old('equipo_id') == $equipo->id ? 'selected' : '' }}>
                                {{ $equipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('equipo_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    
                    @if($equipos->isEmpty())
                        <p class="mt-1 text-sm text-yellow-600">
                            No tienes equipos registrados. 
                            <a href="{{ route('competitor.equipos.create') }}" class="text-blue-600 hover:text-blue-800 underline">Crea uno aquí</a>
                        </p>
                    @endif
                </div>
            </div>
            
            <!-- Notas (opcional) -->
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notas (opcional)</label>
                <textarea name="notes" id="notes" rows="3" 
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Añade cualquier nota relevante para la inscripción...">{{ old('notes') }}</textarea>
            </div>
            
            <!-- Términos y condiciones -->
            <div class="mb-6">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="terms" name="terms" type="checkbox" required
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="terms" class="font-medium text-gray-700">Acepto los términos y condiciones</label>
                        <p class="text-gray-500">Al inscribirme, confirmo que mi robot cumple con los requisitos de la competencia y me comprometo a seguir las reglas del evento.</p>
                    </div>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('competitor.inscripciones.index') }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                    Completar Inscripción
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Script para actualizar los detalles de la competencia cuando se selecciona una
    document.addEventListener('DOMContentLoaded', function() {
        const competitionSelect = document.getElementById('competition_id');
        const competitionInfo = document.getElementById('competition-info');
        
        competitionSelect.addEventListener('change', function() {
            if (this.value) {
                const selectedOption = this.options[this.selectedIndex];
                competitionInfo.classList.remove('hidden');
                competitionInfo.innerHTML = `
                    <h4 class="font-medium text-blue-800 mb-1">${selectedOption.text}</h4>
                    <p class="text-sm text-blue-700">Seleccionada para inscripción</p>
                `;
            } else {
                competitionInfo.classList.add('hidden');
            }
        });
        
        // Disparar el evento change si hay un valor preseleccionado
        if (competitionSelect.value) {
            competitionSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection 