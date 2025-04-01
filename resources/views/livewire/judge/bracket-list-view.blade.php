<div>
    <!-- Contenido de resources/views/filament/judge/pages/bracket-list-view.blade.php pero sin el layout exterior -->
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
                        
                        <!-- Resto de los selectores y filtros -->
                        <!-- ... -->
                    </div>
                </div>
            @endif
        </div>

        <!-- Resto del contenido de la vista original -->
        <!-- ... -->
    </div>
</div> 