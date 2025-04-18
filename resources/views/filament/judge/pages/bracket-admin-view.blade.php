<x-filament-panels::page>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Administración de Bracket: {{ $this->bracket->categoriaEvento->categoria->nombre }} - {{ $this->bracket->categoriaEvento->evento->nombre }}
        </h1>
        <p class="text-gray-600">
            Estado: 
            <span class="font-semibold 
                @if($this->bracket->estado_torneo == 'pendiente') text-yellow-600
                @elseif($this->bracket->estado_torneo == 'en_curso') text-green-600
                @else text-blue-600
                @endif">
                {{ ucfirst($this->bracket->estado_torneo) }}
            </span>
        </p>
    </div>

    <div class="flex justify-between mb-4">
        <!-- Acciones -->
        <div class="flex space-x-4">
            <x-filament::button
                tag="a"
                href="{{ route('judge.brackets.public', $this->bracket->id) }}"
                color="success">
                <i class="fas fa-eye mr-2"></i> Ver Bracket Público
            </x-filament::button>
            
            <x-filament::button
                wire:click="cambiarEstado"
                color="{{ $this->bracket->estado_torneo == 'pendiente' ? 'success' : ($this->bracket->estado_torneo == 'en_curso' ? 'danger' : 'warning') }}"
                x-on:click="confirm('{{ 
                    $this->bracket->estado_torneo == 'pendiente' ? '¿Seguro que desea iniciar el torneo?' :
                    ($this->bracket->estado_torneo == 'en_curso' ? '¿Seguro que desea finalizar el torneo?' : 
                    '¿Seguro que desea reiniciar el torneo? Se perderán todos los resultados.')
                }}') || $event.preventDefault()">
                @if($this->bracket->estado_torneo == 'pendiente')
                    <i class="fas fa-play mr-2"></i> Iniciar Torneo
                @elseif($this->bracket->estado_torneo == 'en_curso')
                    <i class="fas fa-stop mr-2"></i> Finalizar Torneo
                @else
                    <i class="fas fa-redo mr-2"></i> Reiniciar Torneo
                @endif
            </x-filament::button>
        </div>
    </div>

    <!-- Filtros -->
    <x-filament::section>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <x-filament::input.wrapper>
                    <x-filament::input.label for="search">Buscar equipos</x-filament::input.label>
                    <x-filament::input 
                        wire:model.live.debounce.500ms="searchQuery" 
                        id="search"
                        placeholder="Nombre del equipo..." />
                </x-filament::input.wrapper>
            </div>
            
            <div>
                <x-filament::input.wrapper>
                    <x-filament::input.label for="estado_filter">Filtrar por estado</x-filament::input.label>
                    <x-filament::select wire:model.live="estadoFilter" id="estado_filter">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="completado">Completado</option>
                    </x-filament::select>
                </x-filament::input.wrapper>
            </div>
            
            <div>
                <x-filament::input.wrapper>
                    <x-filament::input.label for="ronda_filter">Filtrar por ronda</x-filament::input.label>
                    <x-filament::select wire:model.live="rondaFilter" id="ronda_filter">
                        <option value="">Todas las rondas</option>
                        @foreach($this->rondas as $ronda)
                            <option value="{{ $ronda }}">
                                @if($ronda == $this->bracket->estructura['total_rondas'])
                                    Final
                                @elseif($ronda == $this->bracket->estructura['total_rondas'] - 1)
                                    Semifinal
                                @elseif($ronda == $this->bracket->estructura['total_rondas'] - 2)
                                    Cuartos de final
                                @else
                                    Ronda {{ $ronda }}
                                @endif
                            </option>
                        @endforeach
                    </x-filament::select>
                </x-filament::input.wrapper>
            </div>
            
            <div class="flex items-end">
                <x-filament::button
                    wire:click="filtrar"
                    color="primary">
                    <i class="fas fa-filter mr-2"></i> Filtrar
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    <!-- Progreso del torneo -->
    <x-filament::section>
        <x-slot name="heading">Progreso del torneo</x-slot>
        
        <div class="relative pt-1">
            <div class="overflow-hidden h-4 mb-2 text-xs flex rounded bg-gray-200">
                <div style="width: {{ $this->porcentajeCompletado }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-primary-500">
                    <span class="px-2">{{ $this->porcentajeCompletado }}%</span>
                </div>
            </div>
            <div class="text-sm text-gray-600">
                {{ $this->completados }} de {{ $this->totalEnfrentamientos }} enfrentamientos completados
            </div>
        </div>
    </x-filament::section>

    <!-- Enfrentamientos -->
    @foreach($this->rondas as $ronda)
        <x-filament::section>
            <x-slot name="heading">
                @if($ronda == $this->bracket->estructura['total_rondas'])
                    Final
                @elseif($ronda == $this->bracket->estructura['total_rondas'] - 1)
                    Semifinal
                @elseif($ronda == $this->bracket->estructura['total_rondas'] - 2)
                    Cuartos de final
                @else
                    Ronda {{ $ronda }}
                @endif
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($this->enfrentamientos->where('ronda', $ronda) as $enfrentamiento)
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
                            @if($this->bracket->estado_torneo == 'en_curso' && $enfrentamiento->equipo1 && $enfrentamiento->equipo2)
                                @if($enfrentamiento->estado == 'completado')
                                    <x-filament::button
                                        wire:click="reiniciarResultado({{ $enfrentamiento->id }})"
                                        color="warning"
                                        class="w-full"
                                        x-on:click="confirm('¿Seguro que desea reiniciar este resultado? Se perderán los datos registrados.') || $event.preventDefault()">
                                        <i class="fas fa-redo-alt mr-2"></i> Reiniciar Resultado
                                    </x-filament::button>
                                @else
                                    <x-filament::button
                                        x-data="{}"
                                        x-on:click="$dispatch('open-modal', { id: 'registro-resultado-{{ $enfrentamiento->id }}' })"
                                        color="primary"
                                        class="w-full">
                                        <i class="fas fa-trophy mr-2"></i> Registrar Resultado
                                    </x-filament::button>
                                    
                                    <x-filament::modal id="registro-resultado-{{ $enfrentamiento->id }}">
                                        <x-slot name="heading">Registrar Resultado</x-slot>
                                        
                                        <x-slot name="description">
                                            <p class="text-center text-lg font-medium">
                                                {{ $enfrentamiento->equipo1->nombre }} vs {{ $enfrentamiento->equipo2->nombre }}
                                            </p>
                                        </x-slot>
                                        
                                        <div class="space-y-4" wire:key="modal-content-{{ $enfrentamiento->id }}">
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <x-filament::input.wrapper>
                                                        <x-filament::input.label for="puntuacion_equipo1_{{ $enfrentamiento->id }}">Puntuación {{ $enfrentamiento->equipo1->nombre }}</x-filament::input.label>
                                                        <x-filament::input 
                                                            type="number" 
                                                            id="puntuacion_equipo1_{{ $enfrentamiento->id }}" 
                                                            wire:model="puntuacion_equipo1" 
                                                            min="0" />
                                                    </x-filament::input.wrapper>
                                                </div>
                                                
                                                <div>
                                                    <x-filament::input.wrapper>
                                                        <x-filament::input.label for="puntuacion_equipo2_{{ $enfrentamiento->id }}">Puntuación {{ $enfrentamiento->equipo2->nombre }}</x-filament::input.label>
                                                        <x-filament::input 
                                                            type="number" 
                                                            id="puntuacion_equipo2_{{ $enfrentamiento->id }}" 
                                                            wire:model="puntuacion_equipo2" 
                                                            min="0" />
                                                    </x-filament::input.wrapper>
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <x-filament::input.wrapper>
                                                    <x-filament::input.label>Seleccionar Ganador</x-filament::input.label>
                                                    <div class="grid grid-cols-2 gap-4 mt-2">
                                                        <label class="flex items-center justify-center border border-gray-300 rounded p-3 cursor-pointer hover:bg-gray-50">
                                                            <input type="radio" wire:model="ganador_id" value="{{ $enfrentamiento->equipo1_id }}" class="mr-2" name="ganador_id_{{ $enfrentamiento->id }}">
                                                            <span>{{ $enfrentamiento->equipo1->nombre }}</span>
                                                        </label>
                                                        
                                                        <label class="flex items-center justify-center border border-gray-300 rounded p-3 cursor-pointer hover:bg-gray-50">
                                                            <input type="radio" wire:model="ganador_id" value="{{ $enfrentamiento->equipo2_id }}" class="mr-2" name="ganador_id_{{ $enfrentamiento->id }}">
                                                            <span>{{ $enfrentamiento->equipo2->nombre }}</span>
                                                        </label>
                                                    </div>
                                                </x-filament::input.wrapper>
                                            </div>
                                        </div>
                                        
                                        <x-slot name="footerActions">
                                            <x-filament::button
                                                wire:click="guardarResultado({{ $enfrentamiento->id }}, this.puntuacion_equipo1, this.puntuacion_equipo2, this.ganador_id)"
                                                wire:loading.attr="disabled"
                                                color="success">
                                                Guardar Resultado
                                            </x-filament::button>
                                            
                                            <x-filament::button
                                                x-on:click="$dispatch('close-modal', { id: 'registro-resultado-{{ $enfrentamiento->id }}' })"
                                                color="gray">
                                                Cancelar
                                            </x-filament::button>
                                        </x-slot>
                                    </x-filament::modal>
                                @endif
                            @elseif($this->bracket->estado_torneo == 'pendiente')
                                <span class="text-gray-500 text-sm italic">El torneo debe iniciarse para registrar resultados</span>
                            @elseif($this->bracket->estado_torneo == 'finalizado')
                                <span class="text-gray-500 text-sm italic">El torneo ha finalizado</span>
                            @else
                                <span class="text-gray-500 text-sm italic">Esperando equipos</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endforeach
</x-filament-panels::page> 
