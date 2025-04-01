<x-filament::page>
    <div class="mb-6">
        <div class="w-full lg:w-1/3">
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
    </div>

    @if ($selectedEvento)
        <div class="grid grid-cols-1 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-300 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Estructura de "{{ $this->eventos[$selectedEvento] }}"</h2>
                
                @if ($fechasEvento->isEmpty())
                    <div class="text-center p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <p class="text-gray-500 dark:text-gray-400">No hay fechas configuradas para este evento.</p>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach ($fechasEvento as $fecha)
                            <div class="border rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-900">
                                <div class="bg-primary-100 dark:bg-primary-900 p-4 flex justify-between items-center">
                                    <div>
                                        <h3 class="text-lg font-bold text-primary-800 dark:text-primary-200">
                                            {{ $fecha->nombre }}
                                        </h3>
                                        <p class="text-sm text-primary-600 dark:text-primary-400">
                                            {{ $fecha->fecha_inicio->format('d/m/Y') }} - {{ $fecha->lugar }}
                                        </p>
                                    </div>
                                    <a 
                                        href="{{ route('filament.admin.resources.fecha-eventos.edit', $fecha->id) }}" 
                                        class="text-sm px-3 py-1 rounded-md bg-primary-500 text-white hover:bg-primary-600 transition-colors"
                                        target="_blank"
                                    >
                                        Gestionar
                                    </a>
                                </div>
                                
                                <div class="p-4">
                                    @if ($fecha->categorias->isEmpty())
                                        <p class="text-center py-3 text-gray-500 dark:text-gray-400 italic">
                                            No hay categorías asignadas a esta fecha.
                                        </p>
                                    @else
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            @foreach ($fecha->categorias as $categoria)
                                                <div class="border rounded-md p-3 bg-white dark:bg-gray-800 shadow-sm">
                                                    <div class="flex justify-between items-start">
                                                        <div>
                                                            <span class="font-semibold text-gray-900 dark:text-white">
                                                                {{ $categoria->categoria->nombre }}
                                                            </span>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                Modalidad: {{ $categoria->categoria->modalidad ?? 'No especificada' }}
                                                            </p>
                                                        </div>
                                                        <span 
                                                            @class([
                                                                'text-xs px-2 py-1 rounded-full',
                                                                'bg-gray-100 text-gray-800' => $categoria->estado_competencia === 'creada',
                                                                'bg-blue-100 text-blue-800' => $categoria->estado_competencia === 'inscripciones',
                                                                'bg-yellow-100 text-yellow-800' => $categoria->estado_competencia === 'homologacion',
                                                                'bg-indigo-100 text-indigo-800' => $categoria->estado_competencia === 'armado_llaves',
                                                                'bg-green-100 text-green-800' => $categoria->estado_competencia === 'en_curso',
                                                                'bg-purple-100 text-purple-800' => $categoria->estado_competencia === 'finalizada',
                                                            ])
                                                        >
                                                            {{ ucfirst($categoria->estado_competencia) }}
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="mt-2 flex items-center justify-between text-xs">
                                                        <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full">
                                                            {{ $categoria->inscritos }} inscritos
                                                        </span>
                                                        
                                                        <div class="flex space-x-2">
                                                            @if ($categoria->inscripciones_abiertas)
                                                                <span class="text-green-600">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                    </svg>
                                                                </span>
                                                            @else
                                                                <span class="text-red-600">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                    </svg>
                                                                </span>
                                                            @endif
                                                            
                                                            <span class="text-gray-600 dark:text-gray-400">
                                                                Inscripciones
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                @if ($categoriasEvento->isNotEmpty())
                    <div class="mt-8 border rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-900">
                        <div class="bg-gray-200 dark:bg-gray-800 p-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                Categorías sin fecha asignada
                            </h3>
                        </div>
                        
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($categoriasEvento as $categoria)
                                    <div class="border rounded-md p-3 bg-white dark:bg-gray-800 shadow-sm">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <span class="font-semibold text-gray-900 dark:text-white">
                                                    {{ $categoria->categoria->nombre }}
                                                </span>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    Modalidad: {{ $categoria->categoria->modalidad ?? 'No especificada' }}
                                                </p>
                                            </div>
                                            <span 
                                                @class([
                                                    'text-xs px-2 py-1 rounded-full',
                                                    'bg-gray-100 text-gray-800' => $categoria->estado_competencia === 'creada',
                                                    'bg-blue-100 text-blue-800' => $categoria->estado_competencia === 'inscripciones',
                                                    'bg-yellow-100 text-yellow-800' => $categoria->estado_competencia === 'homologacion',
                                                    'bg-indigo-100 text-indigo-800' => $categoria->estado_competencia === 'armado_llaves',
                                                    'bg-green-100 text-green-800' => $categoria->estado_competencia === 'en_curso',
                                                    'bg-purple-100 text-purple-800' => $categoria->estado_competencia === 'finalizada',
                                                ])
                                            >
                                                {{ ucfirst($categoria->estado_competencia) }}
                                            </span>
                                        </div>
                                        
                                        <div class="mt-2 flex items-center justify-between text-xs">
                                            <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full">
                                                {{ $categoria->inscritos }} inscritos
                                            </span>
                                            
                                            <a 
                                                href="{{ route('filament.admin.resources.categoria-eventos.edit', $categoria->id) }}" 
                                                class="text-xs px-2 py-1 rounded bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors"
                                                target="_blank"
                                            >
                                                Asignar fecha
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="text-center p-8 bg-gray-100 dark:bg-gray-800 rounded-xl">
            <p class="text-gray-500 dark:text-gray-400">Selecciona un evento para ver su estructura jerárquica.</p>
        </div>
    @endif
</x-filament::page> 