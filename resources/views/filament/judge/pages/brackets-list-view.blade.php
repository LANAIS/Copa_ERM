<x-filament::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold dark:text-white">Torneos y Enfrentamientos</h1>
            
            <div class="flex space-x-2">
                <x-filament::button
                    icon="heroicon-o-funnel"
                    icon-position="after"
                    color="gray"
                    outlined
                    wire:click="$toggle('showFilters')"
                >
                    Filtros
                    @if (count($this->tableFilters))
                        <span class="ml-1 rounded-full bg-primary-500 px-1 text-xs font-medium text-white">
                            {{ count($this->tableFilters) }}
                        </span>
                    @endif
                </x-filament::button>
            </div>
        </div>
        
        @if($showFilters ?? false)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{ $this->filtersForm }}
                </div>
            </div>
        @endif
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center mb-6">
                <div class="p-2 bg-primary-100 dark:bg-primary-900 rounded-full mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-700 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold dark:text-white">Gestión de Brackets</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Administre los brackets de las competencias activas</p>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input wire:model.debounce.500ms="tableSearchQuery" type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-primary-500 focus:border-primary-500" placeholder="Buscar brackets por categoría...">
                </div>
            </div>
            
            <!-- Listado en forma de cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                @forelse($this->brackets as $record)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-900 p-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <h3 class="font-medium text-lg text-gray-900 dark:text-white truncate">{{ $record->categoria_nombre }}</h3>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @switch($record->estado_torneo)
                                            @case('pendiente')
                                                bg-gray-100 text-gray-800
                                                @break
                                            @case('en_curso')
                                                bg-green-100 text-green-800
                                                @break
                                            @case('pausado')
                                                bg-yellow-100 text-yellow-800
                                                @break
                                            @case('finalizado')
                                                bg-red-100 text-red-800
                                                @break
                                            @default
                                                bg-gray-100 text-gray-800
                                        @endswitch
                                    ">
                                        {{ match($record->estado_torneo) {
                                            'pendiente' => 'Pendiente',
                                            'en_curso' => 'En Curso',
                                            'pausado' => 'Pausado',
                                            'finalizado' => 'Finalizado',
                                            default => ucfirst($record->estado_torneo),
                                        } }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Tipo de Torneo</span>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                                        {{ match($record->tipo_fixture) {
                                            'eliminacion_directa' => 'Eliminación Directa',
                                            'eliminacion_doble' => 'Eliminación Doble',
                                            'todos_contra_todos' => 'Todos contra Todos',
                                            'suizo' => 'Sistema Suizo',
                                            'grupos' => 'Fase de Grupos',
                                            'fase_grupos_eliminacion' => 'Grupos + Eliminación',
                                            default => ucfirst($record->tipo_fixture),
                                        } }}
                                    </div>
                                </div>
                                <div>
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Enfrentamientos</span>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                                        {{ $record->enfrentamientos_count ?? '0' }} totales
                                    </div>
                                </div>
                            </div>
                            
                            @php
                                $total = $record->enfrentamientos()->count();
                                $completados = $record->enfrentamientos()->whereNotNull('ganador_id')->count();
                                $porcentaje = $total > 0 ? round(($completados / $total) * 100) : 0;
                            @endphp
                            
                            <div class="mb-4">
                                <div class="flex justify-between mb-1">
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Progreso</span>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $completados }}/{{ $total }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                    <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $porcentaje }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $porcentaje }}% completado</div>
                            </div>
                            
                            <div class="flex justify-between mt-4">
                                <a href="{{ route('filament.judge.pages.bracket-public-view', ['id' => $record->id]) }}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-green-700 bg-green-100 rounded-md hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Visualizar
                                </a>
                                <a href="{{ route('filament.judge.pages.bracket-admin.{id}', ['id' => $record->id]) }}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-primary-700 bg-primary-100 rounded-md hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Administrar
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No se encontraron brackets</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Prueba a cambiar los filtros o espere a que se generen nuevos brackets.</p>
                    </div>
                @endforelse
            </div>
            
            <!-- Paginación -->
            @php
                $paginationData = $this->getPaginationData();
            @endphp
            @if($paginationData->hasPages())
                <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $paginationData->links() }}
                </div>
            @endif
        </div>
    </div>
</x-filament::page> 