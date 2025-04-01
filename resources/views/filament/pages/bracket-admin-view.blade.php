<x-filament::page>
    @php
        $bracketData = $this->getBracketData();
        $llave = $bracketData['llave'];
        $equipos = $bracketData['equipos'];
        $tiposTorneo = $bracketData['tiposTorneo'];
    @endphp

    @if($this->confirmingRegenerate)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full dark:bg-gray-800">
            <h3 class="text-lg font-medium mb-4 dark:text-white">Confirmar Regeneración</h3>
            <p class="mb-4 dark:text-gray-300">El bracket ya contiene enfrentamientos. Al regenerarlo, se perderán todos los resultados existentes. ¿Está seguro de continuar?</p>
            
            <div class="flex justify-end space-x-3">
                <x-filament::button
                    color="gray"
                    wire:click="$set('confirmingRegenerate', false)"
                >
                    Cancelar
                </x-filament::button>

                <x-filament::button
                    color="danger"
                    wire:click="confirmarRegenerarBracket"
                >
                    Regenerar Bracket
                </x-filament::button>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Configuración del Tipo de Torneo -->
        <div class="bg-white rounded-xl shadow-sm p-6 dark:bg-gray-800">
            <h2 class="text-lg font-semibold mb-3 dark:text-white">Configuración del Torneo</h2>
            
            <form wire:submit="configurarTipo">
                <div class="mb-4">
                    <label for="tipo_fixture" class="block text-sm font-medium mb-1 dark:text-gray-300">Tipo de Torneo</label>
                    <select id="tipo_fixture" wire:model="tipo_fixture" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @foreach ($tiposTorneo as $valor => $nombre)
                            <option value="{{ $valor }}">
                                {{ $nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" wire:model="usar_cabezas_serie" value="1"
                               class="rounded border-gray-300 text-amber-600 shadow-sm dark:bg-gray-700 dark:border-gray-600">
                        <span class="text-sm dark:text-gray-300">Usar cabezas de serie (seeding)</span>
                    </label>
                </div>
                
                <button type="submit" class="w-full bg-amber-600 text-white py-2 px-4 rounded-md hover:bg-amber-700">
                    Guardar Configuración
                </button>
            </form>
        </div>
        
        <!-- Generación del Bracket -->
        <div class="bg-white rounded-xl shadow-sm p-6 dark:bg-gray-800">
            <h2 class="text-lg font-semibold mb-3 dark:text-white">Generar Bracket</h2>
            
            @if ($llave->estado_torneo === 'pendiente' || $llave->estado_torneo === 'en_curso')
                <form wire:submit="generarBracket">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1 dark:text-gray-300">Equipos Participantes</label>
                        <div class="max-h-48 overflow-y-auto border border-gray-300 rounded-md p-2 dark:border-gray-600">
                            @foreach ($equipos as $equipo)
                                <label class="flex items-center space-x-3 py-1">
                                    <input type="checkbox" wire:model="equipos_seleccionados" value="{{ $equipo->id }}" 
                                           class="rounded border-gray-300 text-amber-600 shadow-sm dark:bg-gray-700 dark:border-gray-600">
                                    <span class="dark:text-gray-300">{{ $equipo->nombre }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-2 text-right">
                            <button type="button" wire:click="seleccionarTodos" class="text-xs text-blue-600 dark:text-blue-400">Seleccionar todos</button> | 
                            <button type="button" wire:click="deseleccionarTodos" class="text-xs text-blue-600 dark:text-blue-400">Deseleccionar todos</button>
                        </div>
                    </div>
                    
                    <!-- Opciones específicas según el tipo de torneo -->
                    <div id="opciones-suizo" class="mb-4" 
                         x-data="{}" 
                         x-show="$wire.tipo_fixture === 'suizo'">
                        <label for="rondas" class="block text-sm font-medium mb-1 dark:text-gray-300">Número de Rondas</label>
                        <input type="number" id="rondas" wire:model="rondas" min="2" max="10"
                               class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    
                    <div id="opciones-grupos" class="mb-4"
                         x-data="{}" 
                         x-show="$wire.tipo_fixture === 'grupos' || $wire.tipo_fixture === 'fase_grupos_eliminacion'">
                        <label for="num_grupos" class="block text-sm font-medium mb-1 dark:text-gray-300">Número de Grupos</label>
                        <input type="number" id="num_grupos" wire:model="num_grupos" min="2" max="8"
                               class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    
                    <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">
                        {{ $llave->enfrentamientos()->count() > 0 ? 'Regenerar Bracket' : 'Generar Bracket' }}
                    </button>
                </form>
            @else
                <div class="p-4 bg-yellow-50 text-yellow-700 rounded-md dark:bg-yellow-800/30 dark:text-yellow-400">
                    El torneo ya ha sido finalizado. No se puede generar un nuevo bracket.
                </div>
            @endif
        </div>
        
        <!-- Control del Torneo -->
        <div class="bg-white rounded-xl shadow-sm p-6 dark:bg-gray-800">
            <h2 class="text-lg font-semibold mb-3 dark:text-white">Control del Torneo</h2>
            
            <div class="mb-4">
                <div class="py-2 px-3 bg-gray-100 rounded-md mb-2 dark:bg-gray-700">
                    <span class="font-medium dark:text-gray-300">Estado actual:</span>
                    <span class="px-2 py-1 rounded-full text-sm 
                        @if($llave->estado_torneo == 'pendiente') bg-gray-200 text-gray-800
                        @elseif($llave->estado_torneo == 'en_curso') bg-green-200 text-green-800
                        @elseif($llave->estado_torneo == 'pausado') bg-yellow-200 text-yellow-800
                        @elseif($llave->estado_torneo == 'finalizado') bg-red-200 text-red-800
                        @endif">
                        {{ ucfirst($llave->estado_torneo) }}
                    </span>
                </div>
                
                <div class="flex flex-col space-y-2">
                    @if ($llave->estado_torneo === 'pendiente')
                        <button wire:click="iniciarTorneo" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">
                            Iniciar Torneo
                        </button>
                    @endif
                    
                    @if ($llave->estado_torneo === 'en_curso')
                        <button wire:click="finalizarTorneo" class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700">
                            Finalizar Torneo
                        </button>
                    @endif
                    
                    @if (in_array($llave->estado_torneo, ['en_curso', 'finalizado']))
                        <button wire:click="reiniciarTorneo" 
                               wire:confirm="¿Está seguro de reiniciar el torneo? Se eliminarán todos los resultados."
                               class="w-full bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700">
                            Reiniciar Torneo
                        </button>
                    @endif
                    
                    <a href="/admin/brackets/{{ $llave->id }}" 
                       class="block text-center bg-amber-600 text-white py-2 px-4 rounded-md hover:bg-amber-700">
                        Ver Bracket
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    @if ($llave->enfrentamientos()->count() > 0)
    <div class="mt-6">
        <div class="bg-white rounded-xl shadow-sm p-6 dark:bg-gray-800">
            <h2 class="text-lg font-semibold mb-4 dark:text-white">Resultados de Enfrentamientos</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <th class="px-4 py-2 border dark:border-gray-600">Ronda</th>
                            <th class="px-4 py-2 border dark:border-gray-600">Equipo 1</th>
                            <th class="px-4 py-2 border dark:border-gray-600">Puntaje</th>
                            <th class="px-4 py-2 border dark:border-gray-600">Equipo 2</th>
                            <th class="px-4 py-2 border dark:border-gray-600">Puntaje</th>
                            <th class="px-4 py-2 border dark:border-gray-600">Ganador</th>
                            <th class="px-4 py-2 border dark:border-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($llave->enfrentamientos()->orderBy('ronda')->orderBy('posicion')->get() as $enfrentamiento)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-2 border dark:border-gray-600">{{ $enfrentamiento->ronda }}</td>
                            <td class="px-4 py-2 border dark:border-gray-600">
                                {{ $enfrentamiento->equipo1 ? $enfrentamiento->equipo1->nombre : 'TBD' }}
                            </td>
                            <td class="px-4 py-2 border dark:border-gray-600 text-center">
                                {{ $enfrentamiento->puntaje_equipo1 ?? '-' }}
                            </td>
                            <td class="px-4 py-2 border dark:border-gray-600">
                                {{ $enfrentamiento->equipo2 ? $enfrentamiento->equipo2->nombre : 'TBD' }}
                            </td>
                            <td class="px-4 py-2 border dark:border-gray-600 text-center">
                                {{ $enfrentamiento->puntaje_equipo2 ?? '-' }}
                            </td>
                            <td class="px-4 py-2 border dark:border-gray-600">
                                @if ($enfrentamiento->ganador)
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs dark:bg-green-800/30 dark:text-green-400">
                                        {{ $enfrentamiento->ganador->nombre }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs dark:bg-gray-800 dark:text-gray-400">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 border dark:border-gray-600">
                                @if ($enfrentamiento->equipo1_id && $enfrentamiento->equipo2_id)
                                <button 
                                    wire:click="editarResultado({{ $enfrentamiento->id }})"
                                    class="px-2 py-1 bg-amber-600 text-white rounded hover:bg-amber-700 text-xs">
                                    Editar
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($mostrarModalResultado && $editingEnfrentamiento)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full dark:bg-gray-800">
            <h3 class="text-lg font-medium mb-4 dark:text-white">Editar Resultado del Enfrentamiento</h3>
            
            <form wire:submit.prevent="guardarResultado">
                <div class="grid grid-cols-5 gap-4 mb-4">
                    <div class="col-span-2">
                        <span class="block text-sm font-medium mb-1 dark:text-gray-300">
                            {{ $editingEnfrentamiento->equipo1 ? $editingEnfrentamiento->equipo1->nombre : 'TBD' }}
                        </span>
                        <input type="number" 
                               wire:model="puntaje_equipo1" 
                               min="0" 
                               class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                               placeholder="Puntos">
                    </div>
                    
                    <div class="col-span-1 flex items-center justify-center">
                        <span class="text-lg font-medium dark:text-white">VS</span>
                    </div>
                    
                    <div class="col-span-2">
                        <span class="block text-sm font-medium mb-1 dark:text-gray-300">
                            {{ $editingEnfrentamiento->equipo2 ? $editingEnfrentamiento->equipo2->nombre : 'TBD' }}
                        </span>
                        <input type="number" 
                               wire:model="puntaje_equipo2" 
                               min="0" 
                               class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                               placeholder="Puntos">
                    </div>
                </div>
                
                @error('puntaje_equipo1')
                    <div class="text-red-500 text-sm mb-2">{{ $message }}</div>
                @enderror
                @error('puntaje_equipo2')
                    <div class="text-red-500 text-sm mb-2">{{ $message }}</div>
                @enderror
                
                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" 
                            wire:click="cancelarEdicionResultado" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        Cancelar
                    </button>
                    
                    <button type="submit" 
                            class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</x-filament::page> 