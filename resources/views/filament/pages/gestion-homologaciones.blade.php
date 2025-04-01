<x-filament-panels::page>
    <div class="mb-4">
        @if($this->categoriaEvento)
            <h2 class="text-2xl font-bold">Homologaciones - {{ $this->categoriaEvento->categoria->nombre }}</h2>
            <p class="text-sm text-gray-500">Evento: {{ $this->categoriaEvento->evento->nombre }}</p>
        @else
            <h2 class="text-2xl font-bold">Seleccione una categoría para gestionar homologaciones</h2>
        @endif
    </div>

    @if($this->categoriaEvento)
        <div class="mb-8">
            <div class="flex justify-end mb-4">
                <button
                    class="px-4 py-2 bg-success-600 text-white rounded-md hover:bg-success-700 inline-flex items-center"
                    wire:click="finalizarHomologaciones"
                >
                    <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Finalizar Homologaciones
                </button>
            </div>

            @if($this->mostrarFormulario && $this->robotEnEdicion)
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-xl font-semibold mb-4">
                        {{ $this->homologacionId ? 'Editar' : 'Registrar' }} Homologación - {{ $this->robotEnEdicion->nombre }}
                    </h3>
                    
                    <form wire:submit.prevent="guardarHomologacion" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1 font-medium text-sm">Peso (kg)</label>
                                <input type="number" step="0.01" wire:model="peso" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                            </div>
                            <div>
                                <label class="block mb-1 font-medium text-sm">Ancho (cm)</label>
                                <input type="number" step="0.1" wire:model="ancho" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                            </div>
                            <div>
                                <label class="block mb-1 font-medium text-sm">Largo (cm)</label>
                                <input type="number" step="0.1" wire:model="largo" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                            </div>
                            <div>
                                <label class="block mb-1 font-medium text-sm">Alto (cm)</label>
                                <input type="number" step="0.1" wire:model="alto" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block mb-1 font-medium text-sm">Resultado</label>
                            <select wire:model="resultado" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                                <option value="">Seleccione un resultado</option>
                                <option value="aprobado">Aprobado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block mb-1 font-medium text-sm">Observaciones</label>
                            <textarea wire:model="observaciones" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" rows="3" placeholder="Ingrese observaciones adicionales..."></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-2 mt-6">
                            <button type="button" wire:click="cancelarFormulario" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                Cancelar
                            </button>
                            
                            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow">
                <div class="p-4">
                    <h3 class="text-lg font-semibold mb-4">Robots Inscritos</h3>
                    
                    @if($this->inscripciones->count() > 0)
                        @foreach($this->inscripciones as $inscripcion)
                            <div class="border-b border-gray-200 py-4 last:border-b-0">
                                <h4 class="font-medium text-lg">Equipo: {{ $inscripcion->equipo->nombre }}</h4>
                                
                                @if($inscripcion->robots->count() > 0)
                                    <div class="mt-2 overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead>
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dimensiones</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($inscripcion->robots as $robot)
                                                    @php
                                                        $homologacion = $robot->homologaciones->where('categoria_evento_id', $this->categoriaEventoId)->first();
                                                    @endphp
                                                    <tr>
                                                        <td class="px-4 py-3 whitespace-nowrap">
                                                            <div class="text-sm font-medium text-gray-900">{{ $robot->nombre }}</div>
                                                            <div class="text-xs text-gray-500">{{ $robot->modalidad }}</div>
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap">
                                                            @if($homologacion)
                                                                <div class="text-xs">
                                                                    <span class="block">Peso: {{ $homologacion->peso }} kg</span>
                                                                    <span class="block">Dimensiones: {{ $homologacion->ancho }}x{{ $homologacion->largo }}x{{ $homologacion->alto }} cm</span>
                                                                </div>
                                                            @else
                                                                <span class="text-xs text-gray-500">No registrado</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap">
                                                            @if($homologacion)
                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                    {{ $homologacion->resultado == 'aprobado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                                    {{ ucfirst($homologacion->resultado) }}
                                                                </span>
                                                            @else
                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                                    Pendiente
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                                            <button 
                                                                class="px-3 py-1 text-xs bg-primary-600 text-white rounded-md hover:bg-primary-700"
                                                                wire:click="mostrarFormularioHomologacion({{ $robot->id }})"
                                                            >
                                                                {{ $homologacion ? 'Editar' : 'Homologar' }}
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 mt-2">No hay robots registrados para este equipo.</p>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="py-8 text-center">
                            <p class="text-gray-500">No hay inscripciones aprobadas para esta categoría.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page> 