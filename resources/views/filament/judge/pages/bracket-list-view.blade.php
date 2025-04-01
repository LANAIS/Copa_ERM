<x-filament-panels::page>
    <div class="space-y-8">
        <!-- Panel de filtros -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 sm:p-6 bg-gradient-to-r from-indigo-600 to-blue-600 text-white">
                <div class="flex flex-row justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold">Explorador de Torneos</h2>
                        <p class="text-sm text-white/80">Visualice y gestione brackets por evento, fecha y categoría</p>
                    </div>
                    <div class="flex space-x-2">
                        <button
                            wire:click="loadEventos"
                            class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors"
                        >
                            <span>Cargar Datos</span>
                        </button>
                        <button
                            wire:click="toggleFilters"
                            class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            @if ($showFilters)
                <div class="p-4 sm:p-6 space-y-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Selector de evento -->
                        <div>
                            <label for="evento-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Evento</label>
                            <select 
                                id="evento-select" 
                                wire:model.live="selectedEventoId"
                                class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm pl-3 pr-10 py-2 text-base focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm w-full"
                            >
                                <option value="">Seleccionar evento</option>
                                @foreach($eventos as $evento)
                                    <option value="{{ $evento['id'] }}">
                                        {{ $evento['nombre'] }} ({{ $evento['fecha'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Selector de fecha -->
                        <div>
                            <label for="fecha-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha</label>
                            <select 
                                id="fecha-select" 
                                wire:model.live="selectedFechaId"
                                @if(empty($fechas)) disabled @endif
                                class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm pl-3 pr-10 py-2 text-base focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm w-full disabled:opacity-50"
                            >
                                <option value="">Seleccionar fecha</option>
                                @foreach($fechas as $fecha)
                                    <option value="{{ $fecha['id'] }}">
                                        {{ $fecha['nombre'] }} ({{ $fecha['fecha'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Selector de categoría -->
                        <div>
                            <label for="categoria-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoría</label>
                            <select 
                                id="categoria-select" 
                                wire:model.live="selectedCategoriaId"
                                @if(empty($categorias)) disabled @endif
                                class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm pl-3 pr-10 py-2 text-base focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm w-full disabled:opacity-50"
                            >
                                <option value="">Seleccionar categoría</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria['id'] }}">
                                        {{ $categoria['nombre'] }} ({{ $categoria['participantes'] }} participantes)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Tabla de llaves disponibles -->
                    @if(!empty($llaves))
                        <div class="mt-4">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Brackets Disponibles</h3>
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-100 dark:bg-gray-800">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Categoría</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Equipos</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                                        @foreach($llaves as $llave)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 {{ $selectedLlaveId == $llave['id'] ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="text-sm text-gray-900 dark:text-gray-200">#{{ $llave['id'] }}</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="text-sm text-gray-900 dark:text-gray-200">{{ $llave['categoria'] }}</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                                        {{ $llave['tipo_fixture'] }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        {{ $llave['estado'] === 'pendiente' ? 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200' : 
                                                           ($llave['estado'] === 'en_curso' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 
                                                           ($llave['estado'] === 'finalizado' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200' : 
                                                           'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200')) }}">
                                                        {{ ucfirst(str_replace('_', ' ', $llave['estado'])) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $llave['equipos'] }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button wire:click="$set('selectedLlaveId', '{{ $llave['id'] }}')" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                        Seleccionar
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @elseif($selectedCategoriaId)
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-900 rounded-lg p-4 text-center mt-4">
                            <svg class="w-6 h-6 text-amber-400 dark:text-amber-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <p class="text-sm text-amber-700 dark:text-amber-400">No se encontraron brackets para esta categoría</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Visualización del bracket seleccionado -->
        @if($selectedLlave)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-5 sm:px-6 bg-indigo-50 dark:bg-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                Bracket #{{ $selectedLlave->id }}
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                {{ $selectedLlave->categoriaEvento->categoria->nombre ?? 'Sin categoría' }}
                                - {{ $selectedLlave->categoriaEvento->evento->nombre ?? 'Sin evento' }}
                                {{ $selectedLlave->fecha->nombre ? '- ' . $selectedLlave->fecha->nombre : '' }}
                            </p>
                        </div>
                        <div class="mt-2 sm:mt-0 flex space-x-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                {{ $selectedLlave->estado_torneo === 'pendiente' ? 'bg-gray-100 text-gray-800' : 
                                   ($selectedLlave->estado_torneo === 'en_curso' ? 'bg-green-100 text-green-800' : 
                                   ($selectedLlave->estado_torneo === 'finalizado' ? 'bg-indigo-100 text-indigo-800' : 
                                   'bg-yellow-100 text-yellow-800')) }}">
                                <span class="flex-none {{ $selectedLlave->estado_torneo === 'en_curso' ? 'text-green-500' : 'text-gray-500' }} mr-1">●</span>
                                {{ ucfirst(str_replace('_', ' ', $selectedLlave->estado_torneo)) }}
                            </span>
                            
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $this->formatTipoFixture($selectedLlave->tipo_fixture) }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Visualización de bracket -->
                <div class="p-4 sm:p-6 overflow-x-auto">
                    @php
                        $totalRondas = $maxRonda;
                    @endphp
                    
                    @if(count($enfrentamientos) > 0)
                        <div class="bracket-container" style="min-width: {{ max(800, 200 * $totalRondas) }}px;">
                            <div class="grid grid-cols-{{ $totalRondas }} gap-4">
                                @for ($ronda = 1; $ronda <= $totalRondas; $ronda++)
                                    <div class="flex flex-col space-y-4">
                                        <h3 class="text-center text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Ronda {{ $ronda }}
                                            @if($ronda === $totalRondas)
                                                (Final)
                                            @elseif($ronda === $totalRondas - 1)
                                                (Semifinal)
                                            @elseif($ronda === $totalRondas - 2 && $totalRondas > 3)
                                                (Cuartos de final)
                                            @endif
                                        </h3>
                                        
                                        @php
                                            $enfrentamientosRonda = $this->getEnfrentamientosPorRonda($ronda);
                                            $altura = 100 / max(count($enfrentamientosRonda), 1);
                                        @endphp
                                        
                                        @foreach ($enfrentamientosRonda as $enfrentamiento)
                                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-3 
                                                {{ $enfrentamiento['ganador_id'] ? 'border-l-4 border-l-green-500 dark:border-l-green-600' : '' }}">
                                                <div class="flex flex-col space-y-2">
                                                    <div class="flex justify-between items-center {{ $enfrentamiento['ganador_id'] == $enfrentamiento['equipo1_id'] ? 'bg-green-50 dark:bg-green-900/20 font-medium text-green-700 dark:text-green-400' : '' }} p-2 rounded">
                                                        <span>{{ $enfrentamiento['equipo1']['nombre'] ?? 'TBD' }}</span>
                                                        <span class="font-semibold">{{ $enfrentamiento['puntaje_equipo1'] ?? '-' }}</span>
                                                    </div>
                                                    
                                                    <div class="flex items-center justify-center text-xs text-gray-500 dark:text-gray-400">
                                                        <span>VS</span>
                                                    </div>
                                                    
                                                    <div class="flex justify-between items-center {{ $enfrentamiento['ganador_id'] == $enfrentamiento['equipo2_id'] ? 'bg-green-50 dark:bg-green-900/20 font-medium text-green-700 dark:text-green-400' : '' }} p-2 rounded">
                                                        <span>{{ $enfrentamiento['equipo2']['nombre'] ?? 'TBD' }}</span>
                                                        <span class="font-semibold">{{ $enfrentamiento['puntaje_equipo2'] ?? '-' }}</span>
                                                    </div>
                                                </div>
                                                
                                                @if($enfrentamiento['ganador_id'])
                                                    <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700 text-center text-xs text-gray-500 dark:text-gray-400">
                                                        <span>Ganador: {{ $enfrentamiento['ganador']['nombre'] ?? 'Desconocido' }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12">
                            <div class="rounded-full bg-gray-100 dark:bg-gray-800 p-3 mb-4">
                                <svg class="h-10 w-10 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">No hay enfrentamientos disponibles</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Este bracket aún no tiene enfrentamientos generados</p>
                        </div>
                    @endif
                </div>
            </div>
        @elseif($selectedCategoriaId && empty($llaves))
            <!-- Estado cuando no hay llaves para la categoría seleccionada -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-8 text-center">
                    <div class="rounded-full bg-amber-100 dark:bg-amber-900/20 p-3 mx-auto w-fit mb-4">
                        <svg class="h-10 w-10 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No se encontraron brackets</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No hay brackets generados para esta categoría</p>
                </div>
            </div>
        @elseif($selectedFechaId && empty($categorias))
            <!-- Estado cuando no hay categorías para la fecha seleccionada -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-8 text-center">
                    <div class="rounded-full bg-blue-100 dark:bg-blue-900/20 p-3 mx-auto w-fit mb-4">
                        <svg class="h-10 w-10 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No hay categorías disponibles</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No se encontraron categorías con brackets para esta fecha</p>
                </div>
            </div>
        @elseif($selectedEventoId && empty($fechas))
            <!-- Estado cuando no hay fechas para el evento seleccionado -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-8 text-center">
                    <div class="rounded-full bg-purple-100 dark:bg-purple-900/20 p-3 mx-auto w-fit mb-4">
                        <svg class="h-10 w-10 text-purple-500 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No hay fechas disponibles</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No se encontraron fechas para este evento</p>
                </div>
            </div>
        @else
            <!-- Estado inicial cuando no se ha seleccionado ningún bracket -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-8 text-center">
                    <div class="rounded-full bg-indigo-100 dark:bg-indigo-900/20 p-3 mx-auto w-fit mb-4">
                        <svg class="h-10 w-10 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Seleccione un bracket</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Utilice los filtros superiores para seleccionar un evento, fecha y categoría</p>
                </div>
            </div>
        @endif
    </div>
    
    <style>
        /* Estilos adicionales para el bracket */
        .bracket-container {
            overflow-x: auto;
            padding-bottom: 1rem;
        }
    </style>
</x-filament-panels::page> 