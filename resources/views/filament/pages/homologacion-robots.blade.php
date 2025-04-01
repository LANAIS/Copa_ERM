<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">Homologación de Robots</h2>
            </div>
            
            <div class="prose max-w-none">
                <p>Homologa tus robots para participar en eventos oficiales de la Copa Robotica 2025. Sigue los pasos a continuación para completar el proceso.</p>
            </div>
        </x-filament::section>
        
        <x-filament::section>
            <div class="space-y-6">
                <!-- Wizard de 3 pasos -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <!-- Navegación del Wizard -->
                    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex">
                            <button 
                                wire:click="$set('currentStep', 1)" 
                                class="flex-1 py-4 px-6 {{ $currentStep == 1 ? 'text-primary-600 dark:text-primary-500 font-medium border-b-2 border-primary-600 dark:border-primary-500' : 'text-gray-500 font-medium bg-gray-50 dark:bg-gray-700 dark:text-gray-400' }}"
                            >
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center {{ $currentStep == 1 ? 'bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-500' : 'bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400' }} rounded-full mr-3">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    </div>
                                    <span>Seleccionar Evento</span>
                                </div>
                            </button>
                            
                            <button 
                                wire:click="$set('currentStep', 2)" 
                                class="flex-1 py-4 px-6 {{ $currentStep == 2 ? 'text-primary-600 dark:text-primary-500 font-medium border-b-2 border-primary-600 dark:border-primary-500' : 'text-gray-500 font-medium bg-gray-50 dark:bg-gray-700 dark:text-gray-400' }}"
                            >
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center {{ $currentStep == 2 ? 'bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-500' : 'bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400' }} rounded-full mr-3">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <span>Seleccionar Robot</span>
                                </div>
                            </button>
                            
                            <button 
                                wire:click="$set('currentStep', 3)" 
                                class="flex-1 py-4 px-6 {{ $currentStep == 3 ? 'text-primary-600 dark:text-primary-500 font-medium border-b-2 border-primary-600 dark:border-primary-500' : 'text-gray-500 font-medium bg-gray-50 dark:bg-gray-700 dark:text-gray-400' }}"
                            >
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center {{ $currentStep == 3 ? 'bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-500' : 'bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400' }} rounded-full mr-3">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <span>Homologación</span>
                                </div>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Contenido del paso actual -->
                    <div class="p-6 bg-white dark:bg-gray-800">
                        @if($currentStep == 1)
                            <!-- Paso 1: Seleccionar Evento -->
                            <div class="space-y-6">
                                <!-- Selector de evento -->
                                <div>
                                    <label for="evento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selecciona el evento</label>
                                    <select 
                                        id="evento" 
                                        wire:model.live="eventoId" 
                                        wire:change="updateEventoId($event.target.value)"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                                    >
                                        <option value="">Seleccionar...</option>
                                        @foreach($this->eventosDisponibles as $evento)
                                            <option value="{{ $evento->id }}">{{ $evento->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Información del evento -->
                                @if($this->evento)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                        <h3 class="text-xl font-bold">{{ $this->evento->nombre }}</h3>
                                        
                                        <div class="mt-4 space-y-2">
                                            <div class="flex items-start gap-2">
                                                <span class="font-semibold text-primary-600">Lugar:</span> 
                                                <span class="text-gray-800 dark:text-white">{{ $this->evento->lugar ?: 'No especificado' }}</span>
                                            </div>
                                            
                                            <div class="flex items-start gap-2">
                                                <span class="font-semibold text-primary-600">Inicio:</span> 
                                                <span class="text-gray-800 dark:text-white">{{ $this->evento->fecha_inicio->format('d/m/Y H:i') }}</span>
                                            </div>
                                            
                                            <div class="flex items-start gap-2">
                                                <span class="font-semibold text-primary-600">Fin:</span> 
                                                <span class="text-gray-800 dark:text-white">{{ $this->evento->fecha_fin->format('d/m/Y H:i') }}</span>
                                            </div>
                                            
                                            <div class="flex items-start gap-2">
                                                <span class="font-semibold text-primary-600">Homologaciones:</span>
                                                <span class="text-gray-800 dark:text-white">{{ $this->evento->inicio_inscripciones->format('d/m/Y') }} - {{ $this->evento->fin_inscripciones->format('d/m/Y') }}</span>
                                            </div>
                                            
                                            <div class="flex items-start gap-2">
                                                <span class="font-semibold text-primary-600">Estado:</span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    @if($this->evento->estado === 'abierto') bg-green-100 text-green-800
                                                    @elseif($this->evento->estado === 'cerrado') bg-yellow-100 text-yellow-800
                                                    @elseif($this->evento->estado === 'finalizado') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($this->evento->estado) }}
                                                </span>
                                            </div>
                                            
                                            @if($this->evento->descripcion)
                                                <div class="mt-4">
                                                    <p class="font-semibold text-primary-600">Descripción:</p>
                                                    <p class="text-gray-800 dark:text-white">{{ $this->evento->descripcion }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Selector de categoría -->
                                <div>
                                    <label for="categoria" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selecciona la categoría</label>
                                    <select 
                                        id="categoria" 
                                        wire:model.live="categoriaEventoId"
                                        wire:change="updateCategoriaEventoId($event.target.value)"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                                    >
                                        <option value="">Seleccionar...</option>
                                        @foreach($this->categoriasDisponibles as $categoriaEvento)
                                            <option value="{{ $categoriaEvento->id }}">
                                                {{ $categoriaEvento->categoria->nombre }} 
                                                [{{ $categoriaEvento->inscripciones->count() }} inscripciones] 
                                                @if($categoriaEvento->estado_competencia === 'homologacion')
                                                    [En Homologación]
                                                @elseif($categoriaEvento->estado_competencia !== 'creada')
                                                    [{{ ucfirst($categoriaEvento->estado_competencia) }}]
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Información de la categoría -->
                                @if($this->categoriaEvento)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                        <h3 class="text-xl font-bold">{{ $this->categoriaEvento->categoria->nombre }}</h3>
                                        
                                        <div class="mt-4 space-y-2">
                                            <div class="flex items-start gap-2">
                                                <span class="font-semibold text-primary-600">Participantes:</span> 
                                                <span class="text-gray-800 dark:text-white">{{ $this->categoriaEvento->participantes_min }} - {{ $this->categoriaEvento->participantes_max }}</span>
                                            </div>
                                            
                                            <div class="flex items-start gap-2">
                                                <span class="font-semibold text-primary-600">Estado de competencia:</span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    @if($this->categoriaEvento->estado_competencia === 'creada') bg-gray-100 text-gray-800
                                                    @elseif($this->categoriaEvento->estado_competencia === 'homologacion') bg-blue-100 text-blue-800
                                                    @elseif($this->categoriaEvento->estado_competencia === 'armado_llaves') bg-yellow-100 text-yellow-800
                                                    @elseif($this->categoriaEvento->estado_competencia === 'en_curso') bg-green-100 text-green-800
                                                    @elseif($this->categoriaEvento->estado_competencia === 'finalizada') bg-purple-100 text-purple-800
                                                    @endif">
                                                    @if($this->categoriaEvento->estado_competencia === 'creada') Creada
                                                    @elseif($this->categoriaEvento->estado_competencia === 'homologacion') En Homologación
                                                    @elseif($this->categoriaEvento->estado_competencia === 'armado_llaves') Armado de Llaves
                                                    @elseif($this->categoriaEvento->estado_competencia === 'en_curso') En Curso
                                                    @elseif($this->categoriaEvento->estado_competencia === 'finalizada') Finalizada
                                                    @endif
                                                </span>
                                            </div>
                                            
                                            @if($this->categoriaEvento->requisitos)
                                                <div class="flex items-start gap-2">
                                                    <span class="font-semibold text-primary-600">Requisitos:</span>
                                                    <span class="text-gray-800 dark:text-white">{{ $this->categoriaEvento->requisitos }}</span>
                                                </div>
                                            @endif
                                            
                                            @if($this->categoriaEvento->reglas_especificas)
                                                <div class="flex items-start gap-2">
                                                    <span class="font-semibold text-primary-600">Reglas Específicas:</span>
                                                    <span class="text-gray-800 dark:text-white">{{ $this->categoriaEvento->reglas_especificas }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @elseif($currentStep == 2)
                            <!-- Paso 2: Seleccionar Robot -->
                            <div class="space-y-6">
                                <!-- Selector de Equipo -->
                                <div>
                                    <label for="equipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selecciona el equipo</label>
                                    <select 
                                        id="equipo" 
                                        wire:model.live="equipoId"
                                        wire:change="updateEquipoId($event.target.value)"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                                    >
                                        <option value="">Seleccionar...</option>
                                        @foreach($this->equiposUsuario as $equipo)
                                            <option value="{{ $equipo->id }}">{{ $equipo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Selector de Robot -->
                                <div>
                                    <label for="robot" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selecciona el robot</label>
                                    <select 
                                        id="robot" 
                                        wire:model.live="robotId"
                                        wire:change="updateRobotId($event.target.value)"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                                    >
                                        <option value="">Seleccionar...</option>
                                        @foreach($this->robotsDisponibles as $robot)
                                            <option value="{{ $robot->id }}">{{ $robot->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Información del Robot -->
                                @if($this->robot)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                        <h3 class="text-xl font-bold">{{ $this->robot->nombre }}</h3>
                                        
                                        <div class="mt-4 space-y-2">
                                            <div class="flex items-start gap-2">
                                                <span class="font-semibold text-primary-600">Modalidad:</span> 
                                                <span class="text-gray-800 dark:text-white">{{ $this->robot->modalidad }}</span>
                                            </div>
                                            
                                            @if($this->robot->foto)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/'.$this->robot->foto) }}" alt="{{ $this->robot->nombre }}" class="w-full max-w-md h-auto rounded-lg">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @elseif($currentStep == 3)
                            <!-- Paso 3: Homologación -->
                            <div class="space-y-6">
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 dark:bg-blue-900/20 dark:border-blue-700">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400 dark:text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-blue-700 dark:text-blue-400">
                                                Estás homologando el robot <strong>{{ $this->robot ? $this->robot->nombre : '' }}</strong> para la categoría <strong>{{ $this->categoriaEvento ? $this->categoriaEvento->categoria->nombre : '' }}</strong> del evento <strong>{{ $this->evento ? $this->evento->nombre : '' }}</strong>.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Comentarios de homologación -->
                                <div>
                                    <label for="comentarios" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Comentarios adicionales</label>
                                    <textarea 
                                        id="comentarios" 
                                        wire:model="comentarios" 
                                        rows="3" 
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                                        placeholder="Añade cualquier comentario o detalle relevante sobre tu robot para la homologación"
                                    ></textarea>
                                </div>
                                
                                <!-- Evidencias de homologación -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Evidencias de homologación</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-700 border-dashed rounded-md">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                                <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-primary-600 dark:text-primary-500 hover:text-primary-500 dark:hover:text-primary-400">
                                                    <span>Sube archivos</span>
                                                    <input 
                                                        id="file-upload" 
                                                        wire:model="evidencias" 
                                                        name="file-upload" 
                                                        type="file" 
                                                        multiple 
                                                        class="sr-only"
                                                    >
                                                </label>
                                                <p class="pl-1">o arrastra y suelta</p>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                PNG, JPG, PDF hasta 10MB
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Botones de navegación -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                        <button 
                            type="button" 
                            wire:click="previousStep"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                            {{ $currentStep == 1 ? 'disabled' : '' }}
                        >
                            Volver
                        </button>
                        
                        @if($currentStep < 3)
                            <button 
                                type="button"
                                wire:click="nextStep"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 dark:bg-primary-500 border border-transparent rounded-md shadow-sm hover:bg-primary-700 dark:hover:bg-primary-600"
                            >
                                Continuar
                            </button>
                        @else
                            <button 
                                type="button"
                                wire:click="enviarHomologacion"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 dark:bg-green-500 border border-transparent rounded-md shadow-sm hover:bg-green-700 dark:hover:bg-green-600"
                            >
                                Homologar Robot
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
