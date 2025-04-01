@php
    use function Filament\Support\format_money;
@endphp

<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}
        
        @if($this->categoriaId && $this->fechaId)
            @php
                $categoriaEvento = \App\Models\CategoriaEvento::with(['evento', 'categoria', 'fecha_evento'])->find($this->categoriaId);
            @endphp
            
            @if($categoriaEvento)
                <div class="mt-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex flex-col sm:flex-row justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-primary-600 dark:text-primary-400">
                                {{ $categoriaEvento->evento->nombre }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Fecha: <span class="font-medium">{{ $categoriaEvento->fecha_evento->nombre }} - {{ $categoriaEvento->fecha_evento->lugar }}</span>
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Categoría: <span class="font-medium">{{ $categoriaEvento->categoria->nombre }}</span>
                            </p>
                        </div>
                        <div class="mt-2 sm:mt-0">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                @if($categoriaEvento->estado_competencia == \App\Models\CategoriaEvento::ESTADO_HOMOLOGACION)
                                    bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @elseif($categoriaEvento->estado_competencia == \App\Models\CategoriaEvento::ESTADO_EN_CURSO)
                                    bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @elseif($categoriaEvento->estado_competencia == \App\Models\CategoriaEvento::ESTADO_FINALIZADA)
                                    bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                @else
                                    bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                @endif
                            ">
                                {{ ucfirst(str_replace('_', ' ', $categoriaEvento->estado_competencia ?? 'Desconocido')) }}
                            </span>
                        </div>
                    </div>
                    
                    @if($categoriaEvento->requisitos || $categoriaEvento->reglas_especificas)
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($categoriaEvento->requisitos)
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                    <h3 class="text-sm font-semibold mb-2">Requisitos del robot:</h3>
                                    <div class="text-sm whitespace-pre-line">{{ $categoriaEvento->requisitos }}</div>
                                </div>
                            @endif
                            
                            @if($categoriaEvento->reglas_especificas)
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                    <h3 class="text-sm font-semibold mb-2">Reglas específicas:</h3>
                                    <div class="text-sm whitespace-pre-line">{{ $categoriaEvento->reglas_especificas }}</div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            <div class="mb-4 mt-6">
                <h3 class="text-lg font-medium">
                    Robots inscritos para homologación - 
                    @if($this->getHomologaciones()->count() > 0)
                        <span class="font-bold">{{ $this->getHomologaciones()->count() }}</span> robots
                    @else
                        No hay robots inscritos
                    @endif
                </h3>
                
                @php
                    $pendientes = $this->getHomologaciones()->where('estado', \App\Models\Homologacion::ESTADO_PENDIENTE)->count();
                @endphp
                
                @if($pendientes > 0)
                    <div class="flex justify-end mt-2">
                        <x-filament::button
                            color="warning"
                            icon="heroicon-m-check-badge"
                            x-data="{}"
                            x-on:click="$dispatch('open-modal', { id: 'confirmar-aprobar-todos' })"
                            size="sm"
                        >
                            Aprobar todos los pendientes ({{ $pendientes }})
                        </x-filament::button>
                        
                        <x-filament::modal
                            id="confirmar-aprobar-todos"
                            width="md"
                            heading="Aprobar todas las homologaciones pendientes"
                        >
                            <div class="space-y-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    ¿Estás seguro de que deseas aprobar las {{ $pendientes }} homologaciones pendientes?
                                </p>
                                <p class="text-xs text-amber-600 dark:text-amber-400">
                                    Esta acción aprobará todos los robots pendientes sin revisar sus medidas específicas.
                                </p>
                            </div>
                            
                            <x-slot name="footerActions">
                                <x-filament::button
                                    color="gray"
                                    x-on:click="$dispatch('close-modal', { id: 'confirmar-aprobar-todos' })"
                                >
                                    Cancelar
                                </x-filament::button>
                                
                                <x-filament::button
                                    color="warning"
                                    wire:click="aprobarTodosPendientes"
                                    x-on:click="$dispatch('close-modal', { id: 'confirmar-aprobar-todos' })"
                                >
                                    Sí, aprobar todos
                                </x-filament::button>
                            </x-slot>
                        </x-filament::modal>
                    </div>
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <!-- Stats Cards -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center space-x-4">
                        <div class="rounded-full bg-yellow-100 dark:bg-yellow-900 p-3">
                            <x-heroicon-o-clock class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Pendientes</h4>
                            <p class="text-2xl font-semibold">
                                {{ $this->getHomologaciones()->where('estado', \App\Models\Homologacion::ESTADO_PENDIENTE)->count() }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center space-x-4">
                        <div class="rounded-full bg-green-100 dark:bg-green-900 p-3">
                            <x-heroicon-o-check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Aprobados</h4>
                            <p class="text-2xl font-semibold">
                                {{ $this->getHomologaciones()->where('estado', \App\Models\Homologacion::ESTADO_APROBADO)->count() }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center space-x-4">
                        <div class="rounded-full bg-red-100 dark:bg-red-900 p-3">
                            <x-heroicon-o-x-circle class="w-6 h-6 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Rechazados</h4>
                            <p class="text-2xl font-semibold">
                                {{ $this->getHomologaciones()->where('estado', \App\Models\Homologacion::ESTADO_RECHAZADO)->count() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List of Robots -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
                @foreach($this->getHomologaciones()->sortBy(function($homologacion) {
                    // Orden: 1. Pendientes, 2. Aprobados, 3. Rechazados
                    if ($homologacion->estado === \App\Models\Homologacion::ESTADO_PENDIENTE) return 1;
                    if ($homologacion->estado === \App\Models\Homologacion::ESTADO_APROBADO) return 2;
                    return 3;
                }) as $homologacion)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-t-4 
                    @if($homologacion->estado === \App\Models\Homologacion::ESTADO_APROBADO)
                        border-green-500
                    @elseif($homologacion->estado === \App\Models\Homologacion::ESTADO_RECHAZADO)
                        border-red-500
                    @else
                        border-yellow-500
                    @endif
                    ">
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <h3 class="text-lg font-bold truncate">{{ $homologacion->robot->nombre }}</h3>
                                
                                <div class="flex-shrink-0">
                                    @if($homologacion->estado === \App\Models\Homologacion::ESTADO_APROBADO)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Aprobado
                                        </span>
                                    @elseif($homologacion->estado === \App\Models\Homologacion::ESTADO_RECHAZADO)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Rechazado
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            Pendiente
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Equipo: {{ $homologacion->equipoInscripcion->equipo->nombre ?? 'No asignado' }}
                            </p>
                            
                            <div class="grid grid-cols-2 gap-2 mt-3">
                                <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                                    <h4 class="text-xs text-gray-500 dark:text-gray-400">Peso</h4>
                                    <p class="font-medium">{{ $homologacion->peso }} kg</p>
                                </div>
                                
                                <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                                    <h4 class="text-xs text-gray-500 dark:text-gray-400">Dimensiones</h4>
                                    <p class="font-medium">{{ $homologacion->alto }}×{{ $homologacion->ancho }}×{{ $homologacion->largo }} cm</p>
                                </div>
                            </div>
                            
                            @if($homologacion->observaciones)
                                <div class="mt-3 p-2 bg-gray-50 dark:bg-gray-700 rounded text-sm">
                                    <h4 class="text-xs text-gray-500 dark:text-gray-400 mb-1">Observaciones:</h4>
                                    <p>{{ $homologacion->observaciones }}</p>
                                </div>
                            @endif
                            
                            @if($homologacion->estado === \App\Models\Homologacion::ESTADO_PENDIENTE)
                                <div class="flex space-x-2 mt-4">
                                    <x-filament::button
                                        color="success"
                                        icon="heroicon-m-check-circle"
                                        wire:click="aprobarHomologacion({{ $homologacion->id }})"
                                        class="flex-1"
                                    >
                                        Aprobar
                                    </x-filament::button>
                                    
                                    <x-filament::button
                                        color="danger"
                                        icon="heroicon-m-x-circle"
                                        x-data="{}"
                                        x-on:click="$dispatch('open-modal', { id: 'rechazar-{{ $homologacion->id }}' })"
                                        class="flex-1"
                                    >
                                        Rechazar
                                    </x-filament::button>
                                </div>
                                
                                <x-filament::modal
                                    id="rechazar-{{ $homologacion->id }}"
                                    width="md"
                                    :heading="'Rechazar homologación de ' . $homologacion->robot->nombre"
                                >
                                    <div class="space-y-4">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Por favor, indique el motivo del rechazo:
                                        </p>
                                        
                                        <textarea
                                            wire:model.defer="observaciones.{{ $homologacion->id }}"
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm"
                                            rows="3"
                                        ></textarea>
                                    </div>
                                    
                                    <x-slot name="footerActions">
                                        <x-filament::button
                                            color="gray"
                                            x-on:click="$dispatch('close-modal', { id: 'rechazar-{{ $homologacion->id }}' })"
                                        >
                                            Cancelar
                                        </x-filament::button>
                                        
                                        <x-filament::button
                                            color="danger"
                                            wire:click="rechazarHomologacion({{ $homologacion->id }}, $wire.observaciones.{{ $homologacion->id }} ?? '')"
                                            x-on:click="$dispatch('close-modal', { id: 'rechazar-{{ $homologacion->id }}' })"
                                        >
                                            Confirmar Rechazo
                                        </x-filament::button>
                                    </x-slot>
                                </x-filament::modal>
                            @elseif($homologacion->estado === \App\Models\Homologacion::ESTADO_APROBADO)
                                <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Evaluado por:</span> {{ $homologacion->evaluador->name ?? 'Sistema' }}
                                </div>
                            @elseif($homologacion->estado === \App\Models\Homologacion::ESTADO_RECHAZADO)
                                <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Rechazado por:</span> {{ $homologacion->evaluador->name ?? 'Sistema' }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <x-heroicon-o-clipboard-document-check class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No hay competencia seleccionada</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Por favor, seleccione un evento, una fecha y una categoría para ver los robots inscritos.</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page> 