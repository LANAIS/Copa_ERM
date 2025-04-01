<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filtros -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Filtros</h2>
                <x-filament::button
                    wire:click="toggleFilters"
                    color="gray"
                    size="sm"
                >
                    <x-heroicon-s-funnel class="w-4 h-4 mr-2" />
                    {{ $showFilters ? 'Ocultar Filtros' : 'Mostrar Filtros' }}
                </x-filament::button>
            </div>

            @if($showFilters)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Evento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Evento</label>
                        <select
                            wire:model.live="selectedEventoId"
                            class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        >
                            <option value="">Selecciona un evento</option>
                            @foreach($eventos as $evento)
                                <option value="{{ $evento['id'] }}">
                                    {{ $evento['nombre'] }} ({{ $evento['fecha'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Fecha -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha</label>
                        <select
                            wire:model.live="selectedFechaId"
                            class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            @if(!$selectedEventoId) disabled @endif
                        >
                            <option value="">Selecciona una fecha</option>
                            @foreach($fechas as $fecha)
                                <option value="{{ $fecha['id'] }}">
                                    {{ $fecha['nombre'] }} ({{ $fecha['fecha'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Categoría -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoría</label>
                        <select
                            wire:model.live="selectedCategoriaId"
                            class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            @if(!$selectedFechaId) disabled @endif
                        >
                            <option value="">Selecciona una categoría</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria['id'] }}">
                                    {{ $categoria['nombre'] }} ({{ $categoria['participantes'] }} participantes)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
        </div>

        <!-- Lista de Llaves -->
        @if($selectedCategoriaId)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Llaves Disponibles</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($llaves as $llave)
                        <div class="border dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer {{ $selectedLlaveId == $llave['id'] ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : '' }}"
                             wire:click="$set('selectedLlaveId', {{ $llave['id'] }})">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-medium text-gray-900 dark:text-white">{{ $llave['categoria'] }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $llave['tipo_fixture'] }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $llave['equipos'] }} equipos</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($llave['estado'] === 'pendiente') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200
                                    @elseif($llave['estado'] === 'en_curso') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                    @else bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 @endif">
                                    {{ ucfirst($llave['estado']) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Enfrentamientos -->
        @if($selectedLlaveId)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">Enfrentamientos</h2>
                    <div class="flex space-x-2">
                        @foreach($this->getActions() as $action)
                            {{ $action }}
                        @endforeach
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ronda</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Posición</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Equipo 1</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Equipo 2</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Resultado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($enfrentamientos as $enfrentamiento)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($enfrentamiento['ronda'] == $maxRonda)
                                            <span class="font-semibold text-blue-600 dark:text-blue-400">Final</span>
                                        @elseif($enfrentamiento['ronda'] == $maxRonda - 1)
                                            <span class="font-semibold text-blue-600 dark:text-blue-400">Semifinal</span>
                                        @elseif($enfrentamiento['ronda'] == $maxRonda - 2)
                                            <span class="font-semibold text-blue-600 dark:text-blue-400">Cuartos de final</span>
                                        @else
                                            Ronda {{ $enfrentamiento['ronda'] }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-white">{{ $enfrentamiento['posicion'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($enfrentamiento['equipo1'])
                                            <span class="{{ $enfrentamiento['ganador_id'] == $enfrentamiento['equipo1_id'] ? 'font-semibold text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white' }}">
                                                {{ $enfrentamiento['equipo1']['nombre'] }}
                                            </span>
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($enfrentamiento['equipo2'])
                                            <span class="{{ $enfrentamiento['ganador_id'] == $enfrentamiento['equipo2_id'] ? 'font-semibold text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white' }}">
                                                {{ $enfrentamiento['equipo2']['nombre'] }}
                                            </span>
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-white">
                                        @if($enfrentamiento['ganador_id'])
                                            {{ $enfrentamiento['puntaje_equipo1'] }} - {{ $enfrentamiento['puntaje_equipo2'] }}
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($enfrentamiento['ganador_id'])
                                            <span class="px-2 py-1 text-xs font-semibold bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full">
                                                Completado
                                            </span>
                                        @elseif($enfrentamiento['equipo1_id'] && $enfrentamiento['equipo2_id'])
                                            <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-full">
                                                En curso
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-full">
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if(!$enfrentamiento['ganador_id'])
                                            <x-filament::button
                                                wire:click="$dispatch('open-modal', { id: 'cargar-resultado-{{ $enfrentamiento['id'] }}' })"
                                                color="success"
                                                size="sm"
                                            >
                                                Cargar Resultado
                                            </x-filament::button>
                                        @else
                                            <x-filament::button
                                                wire:click="$dispatch('open-modal', { id: 'editar-resultado-{{ $enfrentamiento['id'] }}' })"
                                                color="warning"
                                                size="sm"
                                            >
                                                Editar
                                            </x-filament::button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Modales -->
    <x-filament::modal
        id="gestionar-llave"
        width="md"
        :heading="'Gestionar Llave: ' . ($selectedLlave?->categoriaEvento?->categoria?->nombre ?? '')"
    >
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado de la Llave</label>
                    <select
                        wire:model="selectedLlave.estado_torneo"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    >
                        <option value="pendiente">Pendiente</option>
                        <option value="en_curso">En Curso</option>
                        <option value="finalizado">Finalizado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Fixture</label>
                    <select
                        wire:model="selectedLlave.tipo_fixture"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    >
                        <option value="eliminacion_directa">Eliminación Directa</option>
                        <option value="eliminacion_doble">Eliminación Doble</option>
                        <option value="todos_contra_todos">Todos contra Todos</option>
                        <option value="suizo">Sistema Suizo</option>
                        <option value="grupos">Fase de Grupos</option>
                        <option value="fase_grupos_eliminacion">Grupos + Eliminación</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-2">
                <x-filament::button
                    wire:click="$dispatch('close-modal', { id: 'gestionar-llave' })"
                    color="gray"
                >
                    Cancelar
                </x-filament::button>

                <x-filament::button
                    wire:click="guardarCambiosLlave"
                    color="primary"
                >
                    Guardar Cambios
                </x-filament::button>
            </div>
        </div>
    </x-filament::modal>

    <x-filament::modal
        id="crear-enfrentamiento"
        width="md"
        heading="Crear Nuevo Enfrentamiento"
    >
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ronda</label>
                    <input
                        type="number"
                        wire:model="nuevoEnfrentamiento.ronda"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        min="1"
                        max="{{ $maxRonda }}"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Posición</label>
                    <input
                        type="number"
                        wire:model="nuevoEnfrentamiento.posicion"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        min="1"
                    >
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Equipo 1</label>
                    <select
                        wire:model="nuevoEnfrentamiento.equipo1_id"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    >
                        <option value="">Selecciona un equipo</option>
                        @foreach($selectedLlave->estructura['equipos'] ?? [] as $equipo)
                            <option value="{{ $equipo['id'] }}">{{ $equipo['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Equipo 2</label>
                    <select
                        wire:model="nuevoEnfrentamiento.equipo2_id"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    >
                        <option value="">Selecciona un equipo</option>
                        @foreach($selectedLlave->estructura['equipos'] ?? [] as $equipo)
                            <option value="{{ $equipo['id'] }}">{{ $equipo['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-2">
                <x-filament::button
                    wire:click="$dispatch('close-modal', { id: 'crear-enfrentamiento' })"
                    color="gray"
                >
                    Cancelar
                </x-filament::button>

                <x-filament::button
                    wire:click="crearEnfrentamiento"
                    color="primary"
                >
                    Crear Enfrentamiento
                </x-filament::button>
            </div>
        </div>
    </x-filament::modal>

    <x-filament::modal
        id="cargar-resultados"
        width="md"
        heading="Cargar Resultados"
    >
        <div class="space-y-4">
            <div class="overflow-y-auto max-h-96">
                @foreach($enfrentamientos as $enfrentamiento)
                    @if(!$enfrentamiento['ganador_id'])
                        <div class="border-b dark:border-gray-700 py-4">
                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $enfrentamiento['equipo1']['nombre'] ?? 'Pendiente' }}</span>
                                    <span class="mx-2 text-gray-500 dark:text-gray-400">vs</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $enfrentamiento['equipo2']['nombre'] ?? 'Pendiente' }}</span>
                                </div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Ronda {{ $enfrentamiento['ronda'] }}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Puntaje {{ $enfrentamiento['equipo1']['nombre'] ?? 'Equipo 1' }}</label>
                                    <input
                                        type="number"
                                        wire:model="resultados.{{ $enfrentamiento['id'] }}.puntaje_equipo1"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                        min="0"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Puntaje {{ $enfrentamiento['equipo2']['nombre'] ?? 'Equipo 2' }}</label>
                                    <input
                                        type="number"
                                        wire:model="resultados.{{ $enfrentamiento['id'] }}.puntaje_equipo2"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                        min="0"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ganador</label>
                                    <select
                                        wire:model="resultados.{{ $enfrentamiento['id'] }}.ganador_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    >
                                        <option value="">Selecciona el ganador</option>
                                        @if($enfrentamiento['equipo1_id'])
                                            <option value="{{ $enfrentamiento['equipo1_id'] }}">{{ $enfrentamiento['equipo1']['nombre'] }}</option>
                                        @endif
                                        @if($enfrentamiento['equipo2_id'])
                                            <option value="{{ $enfrentamiento['equipo2_id'] }}">{{ $enfrentamiento['equipo2']['nombre'] }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="flex justify-end space-x-2">
                <x-filament::button
                    wire:click="$dispatch('close-modal', { id: 'cargar-resultados' })"
                    color="gray"
                >
                    Cancelar
                </x-filament::button>

                <x-filament::button
                    wire:click="guardarResultados"
                    color="primary"
                >
                    Guardar Resultados
                </x-filament::button>
            </div>
        </div>
    </x-filament::modal>

    <!-- Modal para cargar resultado individual -->
    @foreach($enfrentamientos as $enfrentamiento)
        <x-filament::modal
            id="cargar-resultado-{{ $enfrentamiento['id'] }}"
            width="md"
            heading="Cargar Resultado"
        >
            <div class="space-y-4">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $enfrentamiento['equipo1']['nombre'] ?? 'Pendiente' }}</span>
                        <span class="mx-2 text-gray-500 dark:text-gray-400">vs</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $enfrentamiento['equipo2']['nombre'] ?? 'Pendiente' }}</span>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        @if($enfrentamiento['ronda'] == $maxRonda)
                            Final
                        @elseif($enfrentamiento['ronda'] == $maxRonda - 1)
                            Semifinal
                        @elseif($enfrentamiento['ronda'] == $maxRonda - 2)
                            Cuartos de final
                        @else
                            Ronda {{ $enfrentamiento['ronda'] }}
                        @endif
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Puntaje {{ $enfrentamiento['equipo1']['nombre'] ?? 'Equipo 1' }}</label>
                        <input
                            type="number"
                            wire:model="resultados.{{ $enfrentamiento['id'] }}.puntaje_equipo1"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            min="0"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Puntaje {{ $enfrentamiento['equipo2']['nombre'] ?? 'Equipo 2' }}</label>
                        <input
                            type="number"
                            wire:model="resultados.{{ $enfrentamiento['id'] }}.puntaje_equipo2"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            min="0"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ganador</label>
                    <select
                        wire:model="resultados.{{ $enfrentamiento['id'] }}.ganador_id"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    >
                        <option value="">Selecciona el ganador</option>
                        @if($enfrentamiento['equipo1_id'])
                            <option value="{{ $enfrentamiento['equipo1_id'] }}">{{ $enfrentamiento['equipo1']['nombre'] }}</option>
                        @endif
                        @if($enfrentamiento['equipo2_id'])
                            <option value="{{ $enfrentamiento['equipo2_id'] }}">{{ $enfrentamiento['equipo2']['nombre'] }}</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones</label>
                    <textarea
                        wire:model="resultados.{{ $enfrentamiento['id'] }}.observaciones"
                        rows="3"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    ></textarea>
                </div>

                <div class="flex justify-end space-x-2">
                    <x-filament::button
                        wire:click="$dispatch('close-modal', { id: 'cargar-resultado-{{ $enfrentamiento['id'] }}' })"
                        color="gray"
                    >
                        Cancelar
                    </x-filament::button>

                    <x-filament::button
                        wire:click="guardarResultado({{ $enfrentamiento['id'] }})"
                        color="primary"
                    >
                        Guardar Resultado
                    </x-filament::button>
                </div>
            </div>
        </x-filament::modal>

        <!-- Modal para editar resultado -->
        <x-filament::modal
            id="editar-resultado-{{ $enfrentamiento['id'] }}"
            width="md"
            heading="Editar Resultado"
        >
            <div class="space-y-4">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $enfrentamiento['equipo1']['nombre'] ?? 'Pendiente' }}</span>
                        <span class="mx-2 text-gray-500 dark:text-gray-400">vs</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $enfrentamiento['equipo2']['nombre'] ?? 'Pendiente' }}</span>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        @if($enfrentamiento['ronda'] == $maxRonda)
                            Final
                        @elseif($enfrentamiento['ronda'] == $maxRonda - 1)
                            Semifinal
                        @elseif($enfrentamiento['ronda'] == $maxRonda - 2)
                            Cuartos de final
                        @else
                            Ronda {{ $enfrentamiento['ronda'] }}
                        @endif
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Puntaje {{ $enfrentamiento['equipo1']['nombre'] ?? 'Equipo 1' }}</label>
                        <input
                            type="number"
                            wire:model="resultados.{{ $enfrentamiento['id'] }}.puntaje_equipo1"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            min="0"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Puntaje {{ $enfrentamiento['equipo2']['nombre'] ?? 'Equipo 2' }}</label>
                        <input
                            type="number"
                            wire:model="resultados.{{ $enfrentamiento['id'] }}.puntaje_equipo2"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            min="0"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ganador</label>
                    <select
                        wire:model="resultados.{{ $enfrentamiento['id'] }}.ganador_id"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    >
                        <option value="">Selecciona el ganador</option>
                        @if($enfrentamiento['equipo1_id'])
                            <option value="{{ $enfrentamiento['equipo1_id'] }}">{{ $enfrentamiento['equipo1']['nombre'] }}</option>
                        @endif
                        @if($enfrentamiento['equipo2_id'])
                            <option value="{{ $enfrentamiento['equipo2_id'] }}">{{ $enfrentamiento['equipo2']['nombre'] }}</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones</label>
                    <textarea
                        wire:model="resultados.{{ $enfrentamiento['id'] }}.observaciones"
                        rows="3"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    ></textarea>
                </div>

                <div class="flex justify-end space-x-2">
                    <x-filament::button
                        wire:click="$dispatch('close-modal', { id: 'editar-resultado-{{ $enfrentamiento['id'] }}' })"
                        color="gray"
                    >
                        Cancelar
                    </x-filament::button>

                    <x-filament::button
                        wire:click="actualizarResultado({{ $enfrentamiento['id'] }})"
                        color="primary"
                    >
                        Actualizar Resultado
                    </x-filament::button>
                </div>
            </div>
        </x-filament::modal>
    @endforeach
</x-filament-panels::page> 