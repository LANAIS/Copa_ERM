<x-filament-panels::page>
    <div class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Seleccionar Evento</label>
                <select 
                    wire:model.live="selectedEvento" 
                    class="w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                >
                    <option value="">Seleccionar evento...</option>
                    @foreach($this->eventos as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Seleccionar Fecha</label>
                <select 
                    wire:model.live="selectedFecha" 
                    class="w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                    @if(!$selectedEvento || $this->fechasEvento->isEmpty()) disabled @endif
                >
                    <option value="">Todas las fechas</option>
                    @foreach($this->fechasEvento as $fecha)
                        <option value="{{ $fecha->id }}">{{ $fecha->nombre }} ({{ $fecha->fecha_inicio->format('d/m/Y') }})</option>
                    @endforeach
                </select>
                @if($selectedEvento && $this->fechasEvento->isEmpty())
                    <p class="mt-1 text-sm text-red-500 dark:text-red-400">No hay fechas disponibles para este evento</p>
                @endif
            </div>
        </div>
    </div>

    @if($this->competencias && $this->competencias->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4 mb-6">
            @php
                $colores = [
                    'creada' => 'gray',
                    'inscripciones' => 'blue',
                    'homologacion' => 'warning',
                    'armado_llaves' => 'info',
                    'en_curso' => 'success',
                    'finalizada' => 'purple',
                ];
                
                $estados = [
                    'creada' => 'Creada',
                    'inscripciones' => 'Inscripciones',
                    'homologacion' => 'Homologación',
                    'armado_llaves' => 'Armado de Llaves',
                    'en_curso' => 'En Curso',
                    'finalizada' => 'Finalizada',
                ];
            @endphp
            
            @foreach($this->resumenEstados as $estado => $cantidad)
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="mr-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $estados[$estado] ?? ucfirst($estado) }}</p>
                            <p class="text-2xl font-bold mt-1">{{ $cantidad }}</p>
                        </div>
                        <div>
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-{{ $colores[$estado] }}-100 text-{{ $colores[$estado] }}-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Competencias por Categoría</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Categoría</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Modalidad</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bracket</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Inscritos</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->competencias as $competencia)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $competencia->categoria->nombre }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $competencia->categoria->modalidad ?? $competencia->categoria->tipo ?? 'No especificada' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $colores[$competencia->estado_competencia] ?? 'gray' }}-100 text-{{ $colores[$competencia->estado_competencia] ?? 'gray' }}-800">
                                        {{ $estados[$competencia->estado_competencia] ?? ucfirst($competencia->estado_competencia) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($competencia->llave)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($competencia->llave->estado_torneo == 'pendiente') bg-gray-100 text-gray-800
                                            @elseif($competencia->llave->estado_torneo == 'en_curso') bg-green-100 text-green-800
                                            @elseif($competencia->llave->estado_torneo == 'finalizado') bg-purple-100 text-purple-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($competencia->llave->estado_torneo) }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Sin Bracket
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $competencia->inscritos }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                    <!-- Menú de acciones -->
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open" type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                            </svg>
                                        </button>

                                        <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-10">
                                            <div class="py-1" role="menu" aria-orientation="vertical">
                                                <!-- Acciones para cambiar estado -->
                                                <div class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">Cambiar Estado</div>
                                                
                                                @if($competencia->estado_competencia === 'creada')
                                                    <button wire:click="cambiarEstado({{ $competencia->id }}, 'inscripciones')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">Abrir Inscripciones</button>
                                                @endif
                                                
                                                @if($competencia->estado_competencia === 'inscripciones')
                                                    <button wire:click="cambiarEstado({{ $competencia->id }}, 'homologacion')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">Iniciar Homologación</button>
                                                @endif
                                                
                                                @if($competencia->estado_competencia === 'homologacion')
                                                    <button wire:click="cambiarEstado({{ $competencia->id }}, 'armado_llaves')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">Finalizar Homologación</button>
                                                @endif
                                                
                                                @if($competencia->estado_competencia === 'armado_llaves' && $competencia->llave)
                                                    <button wire:click="cambiarEstado({{ $competencia->id }}, 'en_curso')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">Iniciar Competencia</button>
                                                @endif
                                                
                                                @if($competencia->estado_competencia === 'en_curso')
                                                    <button wire:click="cambiarEstado({{ $competencia->id }}, 'finalizada')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">Finalizar Competencia</button>
                                                @endif
                                                
                                                <!-- Enlaces directos -->
                                                <div class="border-t border-gray-200 dark:border-gray-700 mt-1 pt-1">
                                                    <div class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">Acciones</div>
                                                    
                                                    <a href="{{ url('/admin/categoria-eventos/' . $competencia->id . '/edit') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">Editar Categoría</a>
                                                    
                                                    @if($competencia->estado_competencia === 'homologacion')
                                                        <a href="{{ url('/admin/gestion-homologaciones?id=' . $competencia->id) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">Gestionar Homologaciones</a>
                                                    @endif
                                                    
                                                    @if($competencia->llave)
                                                        <a href="{{ route('admin.brackets.admin', $competencia->llave->id) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">Administrar Bracket</a>
                                                        <a href="{{ route('brackets.show', $competencia->llave->id) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">Ver Bracket</a>
                                                    @elseif($competencia->estado_competencia === 'armado_llaves')
                                                        <button wire:click="crearBracket({{ $competencia->id }})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">Crear Bracket</button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay competencias</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if(!$this->selectedEvento)
                    Seleccione un evento para ver sus competencias.
                @else
                    No hay competencias configuradas para este evento.
                @endif
            </p>
        </div>
    @endif
</x-filament-panels::page> 