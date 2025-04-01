<x-filament::page>
    @php
        $bracketData = $this->getBracketData();
        $llave = $bracketData['llave'];
        $datos = $bracketData['datos'];
    @endphp

    <!-- Meta tag CSRF para solicitudes POST -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="grid gap-6">
        <!-- Header estilo Challonge -->
        <div class="challonge-header">
            <h1 class="dark:text-white">{{ $datos['torneo']['nombre'] }}</h1>
            <div class="flex justify-between items-center">
                <div class="flex gap-2 text-sm">
                    <div class="bg-white bg-opacity-20 dark:bg-gray-700 rounded px-2 py-1">
                        <span class="font-semibold text-gray-900 dark:text-white">Tipo:</span> 
                        <span class="text-gray-900 dark:text-white">
                        @switch($llave->tipo_fixture)
                            @case('eliminacion_directa')
                                Eliminación Directa
                                @break
                            @case('eliminacion_doble')
                                Eliminación Doble
                                @break
                            @case('todos_contra_todos')
                                Round Robin
                                @break
                            @case('suizo')
                                Sistema Suizo
                                @break
                            @case('grupos')
                                Fase de Grupos
                                @break
                            @case('fase_grupos_eliminacion')
                                Grupos + Eliminación
                                @break
                            @default
                                {{ $llave->tipo_fixture }}
                        @endswitch
                        </span>
                    </div>

                    <div class="bg-white bg-opacity-20 dark:bg-gray-700 rounded px-2 py-1">
                        <span class="font-semibold text-gray-900 dark:text-white">Estado:</span>
                        <span class="inline-block rounded-full text-xs py-0.5 px-2 
                            @if($llave->estado_torneo == 'pendiente') bg-gray-200 text-gray-800
                            @elseif($llave->estado_torneo == 'en_curso') bg-green-500 text-white
                            @elseif($llave->estado_torneo == 'pausado') bg-yellow-500 text-white
                            @elseif($llave->estado_torneo == 'finalizado') bg-red-500 text-white
                            @endif">
                            {{ ucfirst($llave->estado_torneo) }}
                        </span>
                    </div>
                </div>
                
                <div class="flex gap-2 text-sm">
                    <div class="bg-white bg-opacity-20 dark:bg-gray-700 rounded px-2 py-1">
                        <span class="font-semibold text-gray-900 dark:text-white">Equipos:</span> 
                        <span class="text-gray-900 dark:text-white">{{ $llave->estructura['total_equipos'] ?? 0 }}</span>
                    </div>
                    <div class="bg-white bg-opacity-20 dark:bg-gray-700 rounded px-2 py-1">
                        <span class="font-semibold text-gray-900 dark:text-white">Rondas:</span>
                        <span class="text-gray-900 dark:text-white">{{ $llave->estructura['total_rondas'] ?? 0 }}</span>
                    </div>
                    <div class="bg-white bg-opacity-20 dark:bg-gray-700 rounded px-2 py-1">
                        <span class="font-semibold text-gray-900 dark:text-white">Enfrentamientos:</span>
                        <span class="text-gray-900 dark:text-white">{{ $llave->enfrentamientos()->count() }}</span>
                    </div>
                    <div class="bg-white bg-opacity-20 dark:bg-gray-700 rounded px-2 py-1">
                        <span class="font-semibold text-gray-900 dark:text-white">Completados:</span>
                        <span class="text-gray-900 dark:text-white">{{ $llave->enfrentamientos()->whereNotNull('ganador_id')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sistema de pestañas estilo Challonge -->
        <div class="bracket-container bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div class="tabs-challonge px-6 pt-6">
                <div class="tab-challonge active" data-tab="bracket">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2" />
                    </svg>
                    Bracket
                </div>
                <div class="tab-challonge" data-tab="participants">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Participantes
                </div>
                <div class="tab-challonge" data-tab="matches">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Enfrentamientos
                </div>
            </div>
            
            <!-- Contenido de pestañas -->
            <div class="px-6 pb-6">
                <!-- Pestaña Bracket -->
                <div class="tab-content active" id="tab-bracket">
                    <!-- Tutorial para nuevos usuarios -->
                    <div id="tutorial-overlay" class="hidden fixed inset-0 bg-black bg-opacity-70 z-50 flex items-center justify-center">
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-2xl mx-auto transform transition-all duration-300">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-bold text-challonge-primary">Bienvenido al Bracket</h3>
                                <button id="cerrar-tutorial" class="text-gray-500 hover:text-red-500 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="flex items-start gap-4">
                                    <div class="bg-challonge-primary text-white rounded-full h-8 w-8 flex items-center justify-center flex-shrink-0">1</div>
                                    <div>
                                        <h4 class="font-semibold">Navegación por pestañas</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">Usa las pestañas para alternar entre el bracket, la lista de participantes y los enfrentamientos.</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start gap-4">
                                    <div class="bg-challonge-primary text-white rounded-full h-8 w-8 flex items-center justify-center flex-shrink-0">2</div>
                                    <div>
                                        <h4 class="font-semibold">Edición de resultados</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">Haz clic en cualquier enfrentamiento en el bracket o usa los botones "Editar" en la tabla para registrar los resultados.</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start gap-4">
                                    <div class="bg-challonge-primary text-white rounded-full h-8 w-8 flex items-center justify-center flex-shrink-0">3</div>
                                    <div>
                                        <h4 class="font-semibold">Controles de visualización</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">Utiliza los botones de zoom, vista completa, y tema para personalizar tu experiencia. También puedes exportar el bracket como PDF o imagen.</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start gap-4">
                                    <div class="bg-challonge-primary text-white rounded-full h-8 w-8 flex items-center justify-center flex-shrink-0">4</div>
                                    <div>
                                        <h4 class="font-semibold">Filtros en la tabla</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">En la pestaña "Enfrentamientos", puedes buscar equipos específicos y filtrar por ronda o estado.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-between">
                                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <input type="checkbox" id="no-mostrar-tutorial" class="rounded border-gray-300">
                                    No mostrar de nuevo
                                </label>
                                <button id="entendido-tutorial" class="btn-challonge">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Entendido
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Controles de zoom y navegación con tooltips -->
                    <div class="flex flex-wrap gap-4 items-center mb-4 pt-4 toolbar-section">
                        <div class="space-x-1">
                            <button id="zoom-in" class="btn-challonge tooltip-trigger">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                <span class="md:inline hidden">Acercar</span>
                                <span class="tooltip">Ampliar el bracket</span>
                            </button>
                            <button id="zoom-out" class="btn-challonge tooltip-trigger">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd" />
                                </svg>
                                <span class="md:inline hidden">Alejar</span>
                                <span class="tooltip">Reducir el bracket</span>
                            </button>
                            <button id="zoom-reset" class="btn-challonge-secondary tooltip-trigger">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                </svg>
                                <span class="md:inline hidden">Reset</span>
                                <span class="tooltip">Volver al tamaño original</span>
                            </button>
                        </div>
                        
                        <div class="border-l pl-4 flex gap-2">
                            <button id="export-pdf" class="btn-challonge-secondary tooltip-trigger">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="md:inline hidden">PDF</span>
                                <span class="tooltip">Exportar bracket como PDF</span>
                            </button>
                            <button id="export-img" class="btn-challonge-secondary tooltip-trigger">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="md:inline hidden">Imagen</span>
                                <span class="tooltip">Guardar bracket como imagen PNG</span>
                            </button>
                        </div>
                        
                        <div class="border-l pl-4 flex gap-2">
                            <button id="fullscreen" class="btn-challonge-secondary tooltip-trigger">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                                </svg>
                                <span class="md:inline hidden">Pantalla Completa</span>
                                <span class="tooltip">Ver en pantalla completa</span>
                            </button>
                            <button id="toggle-theme" class="btn-challonge-secondary tooltip-trigger">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                                <span class="md:inline hidden">Tema</span>
                                <span class="tooltip">Cambiar entre modo claro y oscuro</span>
                            </button>
                            <button id="mostrar-ayuda" class="btn-challonge-secondary tooltip-trigger">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="md:inline hidden">Ayuda</span>
                                <span class="tooltip">Mostrar tutorial</span>
                            </button>
                        </div>
                    </div>

                    <!-- Leyenda de colores para mejor comprensión -->
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 mb-4 flex items-center justify-center space-x-6 text-sm border border-gray-200 dark:border-gray-700 flex-wrap gap-y-2 leyenda-colores">
                        <div class="flex items-center">
                            <span class="inline-block w-4 h-4 bg-challonge-winner mr-2 rounded"></span>
                            <span class="text-gray-800 dark:text-white">Ganador</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-block w-4 h-4 bg-challonge-loser mr-2 rounded"></span>
                            <span class="text-gray-800 dark:text-white">Perdedor</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-block w-4 h-4 bg-gray-200 dark:bg-gray-600 mr-2 rounded"></span>
                            <span class="text-gray-800 dark:text-white">Pendiente</span>
                        </div>
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-challonge-primary mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                            <span class="text-gray-800 dark:text-white">Clic para editar</span>
                        </div>
                    </div>
                    
                    <!-- Visor de Bracket (estilo Challonge) -->
                    <div class="overflow-auto relative">
                        <div id="brackets-viewer" class="brackets-viewer w-full min-h-[600px]"></div>
                    </div>
                </div>
                
                <!-- Pestaña Participantes -->
                <div class="tab-content" id="tab-participants">
                    <div class="pt-4">
                        <h3 class="text-lg font-semibold mb-4 dark:text-white">Participantes</h3>
                        <table class="tabla-challonge">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Equipo</th>
                                    <th>Victorias</th>
                                    <th>Derrotas</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($datos['participantes'] as $index => $participante)
                                    <tr>
                                        <td class="dark:text-white">{{ $index + 1 }}</td>
                                        <td class="font-semibold dark:text-white">{{ $participante['nombre'] }}</td>
                                        <td class="dark:text-white">
                                            @php
                                                $victorias = $llave->enfrentamientos()->where('ganador_id', $participante['id'])->count();
                                            @endphp
                                            {{ $victorias }}
                                        </td>
                                        <td class="dark:text-white">
                                            @php
                                                $derrotas = $llave->enfrentamientos()
                                                    ->where(function($q) use ($participante) {
                                                        $q->where('equipo1_id', $participante['id'])
                                                          ->orWhere('equipo2_id', $participante['id']);
                                                    })
                                                    ->whereNotNull('ganador_id')
                                                    ->where('ganador_id', '!=', $participante['id'])
                                                    ->count();
                                            @endphp
                                            {{ $derrotas }}
                                        </td>
                                        <td>
                                            @if($llave->enfrentamientos()->where('ganador_id', $participante['id'])->exists())
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs dark:bg-green-800/30 dark:text-white">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs dark:bg-gray-800 dark:text-white">
                                                    Pendiente
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pestaña Enfrentamientos -->
                <div class="tab-content" id="tab-matches">
                    <div class="pt-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold dark:text-white">Enfrentamientos</h3>
                            
                            <div class="flex items-center space-x-2">
                                <div class="relative">
                                    <input type="text" id="buscar-enfrentamiento" placeholder="Buscar equipo..." 
                                        class="pl-8 pr-4 py-1 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <div class="hidden tooltip-help absolute -top-10 left-0 bg-gray-800 text-white text-xs p-2 rounded shadow-lg w-48 z-10">
                                        Escribe el nombre de un equipo para filtrar los enfrentamientos
                                    </div>
                                </div>
                                
                                <select id="filtro-ronda" class="border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white text-sm py-1 px-2 relative">
                                    <option value="todas">Todas las rondas</option>
                                    @foreach (range(1, $llave->estructura['total_rondas'] ?? 3) as $ronda)
                                        <option value="{{ $ronda }}">Ronda {{ $ronda }}</option>
                                    @endforeach
                                    <div class="hidden tooltip-help absolute -top-10 left-0 bg-gray-800 text-white text-xs p-2 rounded shadow-lg w-48 z-10">
                                        Filtra enfrentamientos por ronda específica
                                    </div>
                                </select>
                                
                                <select id="filtro-estado" class="border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white text-sm py-1 px-2 relative">
                                    <option value="todos">Todos los estados</option>
                                    <option value="pendiente">Pendientes</option>
                                    <option value="completado">Completados</option>
                                    <div class="hidden tooltip-help absolute -top-10 left-0 bg-gray-800 text-white text-xs p-2 rounded shadow-lg w-48 z-10">
                                        Filtra por enfrentamientos completados o pendientes
                                    </div>
                                </select>
                            </div>
                        </div>
                        
                        <table class="tabla-challonge">
                            <thead>
                                <tr>
                                    <th>Ronda</th>
                                    <th>Equipo 1</th>
                                    <th class="text-center hidden-mobile">Puntaje</th>
                                    <th>Equipo 2</th>
                                    <th class="text-center hidden-mobile">Puntaje</th>
                                    <th>Ganador</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($llave->enfrentamientos()->orderBy('ronda')->orderBy('posicion')->get() as $enfrentamiento)
                                <tr class="enfrentamiento-row" 
                                    data-ronda="{{ $enfrentamiento->ronda }}" 
                                    data-estado="{{ $enfrentamiento->ganador_id ? 'completado' : 'pendiente' }}"
                                    data-equipo1="{{ $enfrentamiento->equipo1 ? $enfrentamiento->equipo1->nombre : '' }}"
                                    data-equipo2="{{ $enfrentamiento->equipo2 ? $enfrentamiento->equipo2->nombre : '' }}">
                                    <td class="dark:text-white">{{ $enfrentamiento->ronda }}</td>
                                    <td class="font-medium {{ $enfrentamiento->ganador_id === $enfrentamiento->equipo1_id ? 'text-green-600 dark:text-green-400' : 'dark:text-white' }}">
                                        {{ $enfrentamiento->equipo1 ? $enfrentamiento->equipo1->nombre : 'TBD' }}
                                        <span class="md:hidden">{{ $enfrentamiento->puntaje_equipo1 ? '('.$enfrentamiento->puntaje_equipo1.')' : '' }}</span>
                                    </td>
                                    <td class="text-center hidden-mobile {{ $enfrentamiento->ganador_id === $enfrentamiento->equipo1_id ? 'font-bold text-green-600 dark:text-green-400' : 'dark:text-white' }}">
                                        {{ $enfrentamiento->puntaje_equipo1 ?? '-' }}
                                    </td>
                                    <td class="font-medium {{ $enfrentamiento->ganador_id === $enfrentamiento->equipo2_id ? 'text-green-600 dark:text-green-400' : 'dark:text-white' }}">
                                        {{ $enfrentamiento->equipo2 ? $enfrentamiento->equipo2->nombre : 'TBD' }}
                                        <span class="md:hidden">{{ $enfrentamiento->puntaje_equipo2 ? '('.$enfrentamiento->puntaje_equipo2.')' : '' }}</span>
                                    </td>
                                    <td class="text-center hidden-mobile {{ $enfrentamiento->ganador_id === $enfrentamiento->equipo2_id ? 'font-bold text-green-600 dark:text-green-400' : 'dark:text-white' }}">
                                        {{ $enfrentamiento->puntaje_equipo2 ?? '-' }}
                                    </td>
                                    <td>
                                        @if ($enfrentamiento->ganador)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs dark:bg-green-800/30 dark:text-white">
                                                {{ $enfrentamiento->ganador->nombre }}
                                            </span>
                                        @else
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs dark:bg-gray-800 dark:text-white">Pendiente</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($enfrentamiento->equipo1_id && $enfrentamiento->equipo2_id)
                                        <button 
                                            data-enfrentamiento-id="{{ $enfrentamiento->id }}"
                                            data-enfrentamiento="{{ json_encode([
                                                'id' => $enfrentamiento->id,
                                                'opponent1' => [
                                                    'id' => $enfrentamiento->equipo1_id,
                                                    'score' => $enfrentamiento->puntaje_equipo1 ?? 0,
                                                    'nombre' => $enfrentamiento->equipo1 ? $enfrentamiento->equipo1->nombre : 'Equipo 1'
                                                ],
                                                'opponent2' => [
                                                    'id' => $enfrentamiento->equipo2_id,
                                                    'score' => $enfrentamiento->puntaje_equipo2 ?? 0,
                                                    'nombre' => $enfrentamiento->equipo2 ? $enfrentamiento->equipo2->nombre : 'Equipo 2'
                                                ]
                                            ]) }}"
                                            onclick="abrirModalResultado({
                                                id: {{ $enfrentamiento->id }},
                                                opponent1: {
                                                    id: {{ $enfrentamiento->equipo1_id }},
                                                    score: {{ $enfrentamiento->puntaje_equipo1 ?? 0 }},
                                                    nombre: '{{ $enfrentamiento->equipo1 ? addslashes($enfrentamiento->equipo1->nombre) : 'Equipo 1' }}'
                                                },
                                                opponent2: {
                                                    id: {{ $enfrentamiento->equipo2_id }},
                                                    score: {{ $enfrentamiento->puntaje_equipo2 ?? 0 }},
                                                    nombre: '{{ $enfrentamiento->equipo2 ? addslashes($enfrentamiento->equipo2->nombre) : 'Equipo 2' }}'
                                                }
                                            })"
                                            class="btn-challonge py-1 px-3 text-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                            <span class="md:inline hidden">Editar</span>
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
        </div>
    </div>

    <!-- Modal para Editar Resultado -->
    <div id="modal-resultado" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center transition-opacity duration-300 ease-in-out">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md mx-auto w-full transform transition-all duration-300 ease-in-out scale-95 opacity-0">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-challonge-primary dark:text-challonge-primary">Actualizar Resultado</h3>
                <button type="button" id="btn-cerrar-modal" class="text-gray-500 hover:text-red-500 dark:text-gray-300 dark:hover:text-red-400 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form id="form-resultado" class="space-y-4">
                <input type="hidden" name="enfrentamiento_id" id="enfrentamiento_id">
                
                <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg mb-4">
                    <div class="grid grid-cols-5 gap-2 items-center">
                        <div class="col-span-2">
                            <span id="equipo1-nombre" class="font-semibold dark:text-white text-sm md:text-base"></span>
                        </div>
                        
                        <div class="col-span-1 text-center dark:text-white font-bold">
                            VS
                        </div>
                        
                        <div class="col-span-2 text-right">
                            <span id="equipo2-nombre" class="font-semibold dark:text-white text-sm md:text-base"></span>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Puntuación:</label>
                    <div class="grid grid-cols-5 gap-2 items-center">
                        <div class="col-span-2">
                            <div class="relative">
                                <input type="number" name="puntaje_equipo1" id="puntaje_equipo1" 
                                    class="w-full border border-gray-300 rounded-lg p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 
                                    focus:ring-2 focus:ring-challonge-primary focus:border-transparent" 
                                    min="0" required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none opacity-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-span-1 text-center dark:text-white font-bold text-xl">
                            -
                        </div>
                        
                        <div class="col-span-2">
                            <div class="relative">
                                <input type="number" name="puntaje_equipo2" id="puntaje_equipo2" 
                                    class="w-full border border-gray-300 rounded-lg p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 
                                    focus:ring-2 focus:ring-challonge-primary focus:border-transparent" 
                                    min="0" required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none opacity-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="pt-2 border-t border-gray-200 dark:border-gray-700 mt-4">
                    <div class="bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-white p-3 rounded-md text-xs mb-4">
                        <div class="flex">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 flex-shrink-0 text-blue-500 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="font-medium mb-1">Determinación automática del ganador</p>
                                <p>El ganador se determinará automáticamente según el puntaje más alto. En caso de empate, se considerará que no hay ganador.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" id="btn-cancelar" 
                        class="px-4 py-2 bg-gray-300 dark:bg-gray-700 rounded-lg text-gray-800 dark:text-white hover:bg-gray-400 dark:hover:bg-gray-600 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-challonge py-2 px-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Guardar Resultado
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <!-- Necesitamos JQuery y la biblioteca para visualización de brackets -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/brackets-viewer@latest/dist/brackets-viewer.min.js"></script>
    
    <!-- Bibliotecas para exportación de PDF e imágenes -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <!-- Biblioteca para gestos táctiles en móviles -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variables globales para la interfaz
            let currentZoom = 1;
            let startScale = 1;
            let isMobile = window.innerWidth < 768;
            let initialLoad = true;
            
            // Comprobar si se debe mostrar el tutorial
            function comprobarYMostrarTutorial() {
                if (localStorage.getItem('challonge-tutorial-visto') !== 'true') {
                    document.getElementById('tutorial-overlay').classList.remove('hidden');
                }
            }
            
            // Mostrar tutorial después de un breve retraso para permitir que la página se cargue completamente
            setTimeout(comprobarYMostrarTutorial, 1000);
            
            // Manejar el cierre del tutorial
            document.getElementById('cerrar-tutorial').addEventListener('click', function() {
                const noMostrarTutorial = document.getElementById('no-mostrar-tutorial').checked;
                
                if (noMostrarTutorial) {
                    localStorage.setItem('challonge-tutorial-visto', 'true');
                }
                
                document.getElementById('tutorial-overlay').classList.add('hidden');
            });
            
            document.getElementById('entendido-tutorial').addEventListener('click', function() {
                const noMostrarTutorial = document.getElementById('no-mostrar-tutorial').checked;
                
                if (noMostrarTutorial) {
                    localStorage.setItem('challonge-tutorial-visto', 'true');
                }
                
                document.getElementById('tutorial-overlay').classList.add('hidden');
            });
            
            // Mostrar tutorial desde botón de ayuda
            document.getElementById('mostrar-ayuda').addEventListener('click', function() {
                document.getElementById('tutorial-overlay').classList.remove('hidden');
            });

            // Mejorar interacción con filtros - mostrar tooltips al enfocar
            const inputsFiltros = document.querySelectorAll('#buscar-enfrentamiento, #filtro-ronda, #filtro-estado');
            
            inputsFiltros.forEach(input => {
                input.addEventListener('focus', function() {
                    const tooltipHelp = this.nextElementSibling.nextElementSibling;
                    if (tooltipHelp && tooltipHelp.classList.contains('tooltip-help')) {
                        tooltipHelp.classList.remove('hidden');
                        setTimeout(() => {
                            tooltipHelp.classList.add('hidden');
                        }, 3000); // Ocultar después de 3 segundos
                    }
                });
            });

            // Efectos visuales para mejorar la UX
            function agregarAnimacionesUI() {
                // Animar al hacer hover en filas de tabla
                document.querySelectorAll('.tabla-challonge tbody tr').forEach(row => {
                    row.style.transition = 'transform 0.2s, box-shadow 0.2s';
                    
                    row.addEventListener('mouseenter', () => {
                        row.style.transform = 'translateY(-2px)';
                        row.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.05)';
                        row.style.zIndex = '10';
                        row.style.position = 'relative';
                    });
                    
                    row.addEventListener('mouseleave', () => {
                        row.style.transform = 'translateY(0)';
                        row.style.boxShadow = 'none';
                        row.style.zIndex = '1';
                    });
                });
                
                // Destacar pestañas al hacer hover
                document.querySelectorAll('.tab-challonge').forEach(tab => {
                    tab.addEventListener('mouseenter', () => {
                        if (!tab.classList.contains('active')) {
                            tab.style.backgroundColor = 'rgba(0, 0, 0, 0.05)';
                        }
                    });
                    
                    tab.addEventListener('mouseleave', () => {
                        if (!tab.classList.contains('active')) {
                            tab.style.backgroundColor = 'transparent';
                        }
                    });
                });
            }
            
            // Configurar gestos táctiles para el bracket si estamos en móvil
            function configurarGestosTactiles() {
                if (!isMobile) return;
                
                const bracketsContainer = document.getElementById('brackets-viewer');
                if (!bracketsContainer) return;
                
                const hammer = new Hammer(bracketsContainer);
                
                // Configurar reconocimiento de gestos
                hammer.get('pinch').set({ enable: true });
                hammer.get('pan').set({ direction: Hammer.DIRECTION_ALL });
                
                // Detectar pellizco para zoom
                hammer.on('pinchstart', function(e) {
                    startScale = currentZoom;
                });
                
                hammer.on('pinch', function(e) {
                    currentZoom = Math.min(Math.max(startScale * e.scale, 0.5), 2);
                    applyZoom();
                });
                
                // Detectar deslizamiento para mover
                hammer.on('panmove', function(e) {
                    // Mover el container con el deslizamiento
                    const scrollContainer = bracketsContainer.closest('.overflow-auto');
                    if (scrollContainer) {
                        scrollContainer.scrollLeft -= e.deltaX;
                        scrollContainer.scrollTop -= e.deltaY;
                    }
                });
                
                // Doble tap para reset de zoom
                hammer.on('doubletap', function(e) {
                    currentZoom = 1;
                    applyZoom();
                });
                
                // Mensaje para usuarios de dispositivos móviles
                if (initialLoad) {
                    setTimeout(() => {
                        mostrarMensajeInfo('Usa dos dedos para hacer zoom y desliza para navegar');
                        initialLoad = false;
                    }, 2000);
                }
            }
            
            // Llamar a la función después de que la página haya cargado completamente
            setTimeout(agregarAnimacionesUI, 500);
            
            // Configurar gestos táctiles después de que la página haya cargado
            setTimeout(configurarGestosTactiles, 800);
            
            // Ajustes iniciales para móvil
            if (isMobile) {
                // Iniciar con zoom reducido en móviles
                currentZoom = 0.8;
                
                // Auto-scroll al bracket después de cargar para mejor experiencia en móvil
                setTimeout(() => {
                    const bracketsContainer = document.getElementById('brackets-viewer');
                    if (bracketsContainer) {
                        const scrollContainer = bracketsContainer.closest('.overflow-auto');
                        if (scrollContainer) {
                            scrollContainer.scrollIntoView({ behavior: 'smooth' });
                        }
                    }
                }, 1200);
            }
            
            // Datos del bracket
            const datos = @json($datos);
            
            /**
             * Renderizar el bracket usando brackets-viewer
             * Esta biblioteca permite visualizar el bracket de manera interactiva
             */
            window.bracketsViewer.render({
                stages: [{
                    id: 1,
                    name: datos.torneo.nombre,
                    type: convertirTipoAFormatoViewer(datos.torneo.tipo),
                    number: 1,
                    settings: {}
                }],
                matches: datos.matches.map(match => ({
                    id: match.id,
                    number: match.numero_juego,
                    stage_id: 1,
                    group_id: match.grupo ? parseInt(match.grupo.charCodeAt(0) - 64) : null,
                    round_id: match.ronda,
                    opponent1: {
                        id: match.equipo1_id,
                        score: match.puntaje_equipo1,
                        result: (match.ganador_id === match.equipo1_id) ? 'win' : 
                                ((match.ganador_id && match.ganador_id !== match.equipo1_id) ? 'loss' : null)
                    },
                    opponent2: {
                        id: match.equipo2_id,
                        score: match.puntaje_equipo2,
                        result: (match.ganador_id === match.equipo2_id) ? 'win' : 
                                ((match.ganador_id && match.ganador_id !== match.equipo2_id) ? 'loss' : null)
                    },
                    status: match.estado
                })),
                participants: datos.participantes.map(p => ({
                    id: p.id,
                    name: p.nombre,
                    tournament_id: datos.torneo.id
                }))
            }, {
                selector: '#brackets-viewer',
                participantOriginPlacement: 'before',
                showSlotsOrigin: true,
                showFullParticipantNames: true,
                handleParticipantClick: function(participant) {
                    console.log('Participante clickeado:', participant);
                },
                onMatchClick: function(match) {
                    abrirModalResultado(match);
                }
            });
            
            /**
             * Convierte el tipo de torneo al formato esperado por brackets-viewer
             * @param {string} tipo - El tipo de torneo en formato interno
             * @return {string} - El tipo en formato brackets-viewer
             */
            function convertirTipoAFormatoViewer(tipo) {
                switch(tipo) {
                    case 'eliminacion_directa': return 'single_elimination';
                    case 'eliminacion_doble': return 'double_elimination';
                    case 'todos_contra_todos': return 'round_robin';
                    case 'fase_grupos_eliminacion': return 'groups';
                    case 'grupos': return 'groups';
                    case 'suizo': return 'swiss';
                    default: return 'single_elimination';
                }
            }
            
            // Elementos del DOM para el modal
            const modal = document.getElementById('modal-resultado');
            const form = document.getElementById('form-resultado');
            const btnCancelar = document.getElementById('btn-cancelar');
            
            // Cerrar modal al hacer clic en cancelar o en el botón de cerrar
            btnCancelar.addEventListener('click', function() {
                const modalContent = modal.querySelector('.bg-white');
                modalContent.classList.remove('scale-100', 'opacity-100');
                modalContent.classList.add('scale-95', 'opacity-0');
                
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            });

            document.getElementById('btn-cerrar-modal').addEventListener('click', function() {
                const modalContent = modal.querySelector('.bg-white');
                modalContent.classList.remove('scale-100', 'opacity-100');
                modalContent.classList.add('scale-95', 'opacity-0');
                
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            });

            // También cerrar el modal si se hace clic fuera de él
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    const modalContent = modal.querySelector('.bg-white');
                    modalContent.classList.remove('scale-100', 'opacity-100');
                    modalContent.classList.add('scale-95', 'opacity-0');
                    
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 300);
                }
            });
            
            /**
             * Función para abrir el modal de edición de resultado
             * Se expone globalmente para que sea accesible desde los botones
             * y desde el visor de brackets
             * @param {Object} match - Datos del enfrentamiento
             */
            window.abrirModalResultado = function(match) {
                // Solo permitir editar si ambos equipos están asignados
                if (!match.opponent1.id || !match.opponent2.id) {
                    return;
                }
                
                // Buscar los nombres de los equipos
                let equipo1, equipo2;
                
                if (Array.isArray(datos.participantes)) {
                    // Cuando viene desde la visualización del bracket
                    equipo1 = datos.participantes.find(p => p.id === match.opponent1.id);
                    equipo2 = datos.participantes.find(p => p.id === match.opponent2.id);
                } else {
                    // Cuando viene desde el botón de la tabla
                    equipo1 = { nombre: match.opponent1.nombre || 'Equipo 1' };
                    equipo2 = { nombre: match.opponent2.nombre || 'Equipo 2' };
                }
                
                document.getElementById('enfrentamiento_id').value = match.id;
                document.getElementById('equipo1-nombre').textContent = equipo1 ? equipo1.nombre : 'Equipo 1';
                document.getElementById('equipo2-nombre').textContent = equipo2 ? equipo2.nombre : 'Equipo 2';
                document.getElementById('puntaje_equipo1').value = match.opponent1.score || 0;
                document.getElementById('puntaje_equipo2').value = match.opponent2.score || 0;
                
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.querySelector('.bg-white').classList.add('scale-100', 'opacity-100');
                    modal.querySelector('.bg-white').classList.remove('scale-95', 'opacity-0');
                }, 10);
            };
            
            /**
             * Manejador para el envío del formulario de resultado
             * Procesa los datos, determina el ganador y envía al backend
             */
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const enfrentamientoId = document.getElementById('enfrentamiento_id').value;
                const puntaje1 = parseInt(document.getElementById('puntaje_equipo1').value);
                const puntaje2 = parseInt(document.getElementById('puntaje_equipo2').value);
                
                // Determinar ganador automáticamente
                let ganadorId = null;
                
                // Intentar encontrar el enfrentamiento en los datos del bracket
                const enfrentamiento = datos.matches ? datos.matches.find(m => m.id == enfrentamientoId) : null;
                
                console.log('Guardando resultado para enfrentamiento ID:', enfrentamientoId);
                console.log('Puntajes:', puntaje1, '-', puntaje2);
                
                // Si no podemos encontrar el enfrentamiento en datos.matches, buscamos en los atributos de datos
                if (!enfrentamiento) {
                    // Buscar el botón con los datos del enfrentamiento
                    const btnEditar = document.querySelector(`button[data-enfrentamiento-id="${enfrentamientoId}"]`);
                    
                    if (btnEditar && btnEditar.dataset.enfrentamiento) {
                        try {
                            const enfrentamientoData = JSON.parse(btnEditar.dataset.enfrentamiento);
                            
                            console.log('Usando datos del botón para enfrentamiento');
                            
                            if (puntaje1 > puntaje2) {
                                ganadorId = enfrentamientoData.opponent1.id;
                            } else if (puntaje2 > puntaje1) {
                                ganadorId = enfrentamientoData.opponent2.id;
                            }
                        } catch (error) {
                            console.error('Error al parsear datos:', error);
                            mostrarMensajeError('Error al procesar datos del enfrentamiento');
                            return;
                        }
                    } else {
                        console.error('No se encontraron datos para este enfrentamiento');
                        mostrarMensajeError('No se encontraron datos para este enfrentamiento');
                        return;
                    }
                } else {
                    // Usar los datos del objeto enfrentamiento
                    if (puntaje1 > puntaje2) {
                        ganadorId = enfrentamiento.equipo1_id;
                    } else if (puntaje2 > puntaje1) {
                        ganadorId = enfrentamiento.equipo2_id;
                    }
                }
                
                if (!ganadorId && puntaje1 !== puntaje2) {
                    console.error('No se pudo determinar el ganador');
                    mostrarMensajeError('No se pudo determinar el ganador');
                    return;
                }
                
                // Mostrar mensaje de carga
                const submitBtn = form.querySelector('[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Guardando...';
                submitBtn.disabled = true;
                
                // Preparar datos para enviar
                const requestData = {
                    puntaje_equipo1: puntaje1,
                    puntaje_equipo2: puntaje2,
                    ganador_id: ganadorId
                };
                
                // Enviar al backend
                fetch(`/api/enfrentamientos/${enfrentamientoId}/resultado`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error('Error en la respuesta del servidor: ' + response.status + ' ' + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Cerrar modal
                        const modalContent = modal.querySelector('.bg-white');
                        modalContent.classList.remove('scale-100', 'opacity-100');
                        modalContent.classList.add('scale-95', 'opacity-0');
                        
                        setTimeout(() => {
                            modal.classList.add('hidden');
                        }, 300);
                        
                        // Mostrar mensaje de éxito
                        mostrarMensajeExito('Resultado guardado correctamente');
                        
                        // Recargar página después de mostrar el mensaje
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        mostrarMensajeError('Error al guardar: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarMensajeError(error.message);
                })
                .finally(() => {
                    // Restaurar el botón
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
            });
            
            /**
             * Muestra un mensaje de éxito temporal
             * @param {string} mensaje - El mensaje a mostrar
             */
            function mostrarMensajeExito(mensaje) {
                const mensajeElement = document.createElement('div');
                mensajeElement.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
                mensajeElement.textContent = mensaje;
                document.body.appendChild(mensajeElement);
                
                setTimeout(() => {
                    mensajeElement.remove();
                }, 3000);
            }
            
            /**
             * Muestra un mensaje de error temporal
             * @param {string} mensaje - El mensaje de error a mostrar
             */
            function mostrarMensajeError(mensaje) {
                const mensajeElement = document.createElement('div');
                mensajeElement.className = 'fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg z-50';
                mensajeElement.textContent = mensaje;
                document.body.appendChild(mensajeElement);
                
                setTimeout(() => {
                    mensajeElement.remove();
                }, 5000);
            }

            // Controles de zoom y navegación
            let bracketsContainer = document.getElementById('brackets-viewer');

            // Zoom In
            document.getElementById('zoom-in').addEventListener('click', function() {
                if (currentZoom < 2) {
                    currentZoom += 0.1;
                    applyZoom();
                }
            });

            // Zoom Out
            document.getElementById('zoom-out').addEventListener('click', function() {
                if (currentZoom > 0.5) {
                    currentZoom -= 0.1;
                    applyZoom();
                }
            });

            // Reset Zoom
            document.getElementById('zoom-reset').addEventListener('click', function() {
                currentZoom = 1;
                applyZoom();
            });

            function applyZoom() {
                bracketsContainer.style.transform = `scale(${currentZoom})`;
                bracketsContainer.style.transformOrigin = 'top left';
            }
            
            // Aplicar zoom inicial para móviles
            if (isMobile) {
                setTimeout(() => {
                    applyZoom();
                }, 800);
            }
            
            // Manejar cambios de orientación en móvil
            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    isMobile = window.innerWidth < 768;
                    
                    // Reajustar bracket al cambiar orientación
                    if (isMobile && currentZoom > 0.9) {
                        currentZoom = 0.8;
                        applyZoom();
                    }
                }, 300);
            });
            
            // Detectar cambios en el tamaño de la ventana
            let resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                
                resizeTimeout = setTimeout(function() {
                    const wasIsMobile = isMobile;
                    isMobile = window.innerWidth < 768;
                    
                    // Solo actuar si cambia entre móvil y escritorio
                    if (wasIsMobile !== isMobile) {
                        currentZoom = isMobile ? 0.8 : 1;
                        applyZoom();
                        
                        // Reconfigurar gestos táctiles según corresponda
                        if (isMobile) {
                            configurarGestosTactiles();
                        }
                    }
                }, 250);
            });

            // Exportar como PDF
            document.getElementById('export-pdf').addEventListener('click', function() {
                try {
                    mostrarMensajeExito('Preparando PDF para descargar...');
                    
                    // Usamos html2pdf.js para convertir el bracket a PDF
                    const element = document.getElementById('brackets-viewer');
                    const opt = {
                        margin: 1,
                        filename: `bracket-${datos.torneo.nombre.replace(/\s+/g, '-')}.pdf`,
                        image: { type: 'jpeg', quality: 0.98 },
                        html2canvas: { scale: 2 },
                        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
                    };

                    // Añadimos un pequeño retraso para que la UI se actualice antes de generar el PDF
                    setTimeout(() => {
                        html2pdf().set(opt).from(element).save();
                    }, 500);
                } catch (error) {
                    console.error('Error al exportar PDF:', error);
                    mostrarMensajeError('No fue posible exportar el PDF. Intente más tarde.');
                }
            });

            // Exportar como imagen
            document.getElementById('export-img').addEventListener('click', function() {
                try {
                    mostrarMensajeExito('Preparando imagen para descargar...');
                    
                    const element = document.getElementById('brackets-viewer');
                    
                    // Usamos html2canvas para convertir el bracket a imagen
                    html2canvas(element, {
                        scale: 2,
                        backgroundColor: getComputedStyle(element).backgroundColor
                    }).then(canvas => {
                        // Convertir canvas a URL de datos
                        const imgData = canvas.toDataURL('image/png');
                        
                        // Crear enlace de descarga
                        const link = document.createElement('a');
                        link.href = imgData;
                        link.download = `bracket-${datos.torneo.nombre.replace(/\s+/g, '-')}.png`;
                        link.click();
                    });
                } catch (error) {
                    console.error('Error al exportar imagen:', error);
                    mostrarMensajeError('No fue posible exportar la imagen. Intente más tarde.');
                }
            });

            // Pantalla completa
            document.getElementById('fullscreen').addEventListener('click', function() {
                try {
                    const container = document.getElementById('brackets-viewer');
                    
                    if (!document.fullscreenElement) {
                        if (container.requestFullscreen) {
                            container.requestFullscreen();
                        } else if (container.webkitRequestFullscreen) {
                            container.webkitRequestFullscreen();
                        } else if (container.msRequestFullscreen) {
                            container.msRequestFullscreen();
                        }
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        } else if (document.webkitExitFullscreen) {
                            document.webkitExitFullscreen();
                        } else if (document.msExitFullscreen) {
                            document.msExitFullscreen();
                        }
                    }
                } catch (error) {
                    console.error('Error con pantalla completa:', error);
                    mostrarMensajeError('No fue posible cambiar a pantalla completa');
                }
            });

            // Alternar tema
            document.getElementById('toggle-theme').addEventListener('click', function() {
                const isDark = document.documentElement.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'light' : 'dark');
            });

            // Cargar tema preferido al inicio
            document.addEventListener('DOMContentLoaded', function() {
                const savedTheme = localStorage.getItem('theme');
                if (savedTheme) {
                    if (savedTheme === 'dark') {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            });

            // Filtros para la tabla de enfrentamientos
            const buscarInput = document.getElementById('buscar-enfrentamiento');
            const filtroRonda = document.getElementById('filtro-ronda');
            const filtroEstado = document.getElementById('filtro-estado');
            const filas = document.querySelectorAll('.enfrentamiento-row');

            function aplicarFiltros() {
                const textoBusqueda = buscarInput.value.toLowerCase();
                const rondaSeleccionada = filtroRonda.value;
                const estadoSeleccionado = filtroEstado.value;
                
                filas.forEach(fila => {
                    const ronda = fila.dataset.ronda;
                    const estado = fila.dataset.estado;
                    const equipo1 = fila.dataset.equipo1.toLowerCase();
                    const equipo2 = fila.dataset.equipo2.toLowerCase();
                    const textoCoincide = equipo1.includes(textoBusqueda) || equipo2.includes(textoBusqueda);
                    
                    const coincideRonda = rondaSeleccionada === 'todas' || ronda === rondaSeleccionada;
                    const coincideEstado = estadoSeleccionado === 'todos' || estado === estadoSeleccionado;
                    
                    if (textoCoincide && coincideRonda && coincideEstado) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                });
            }

            buscarInput.addEventListener('input', aplicarFiltros);
            filtroRonda.addEventListener('change', aplicarFiltros);
            filtroEstado.addEventListener('change', aplicarFiltros);

            // Tabs functionality
            const tabs = document.querySelectorAll('.tab-challonge');
            const tabContents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const tabId = tab.getAttribute('data-tab');
                    
                    // Desactivar todas las pestañas
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Activar la pestaña seleccionada
                    tab.classList.add('active');
                    document.getElementById(`tab-${tabId}`).classList.add('active');
                    
                    // Si es la pestaña de bracket, reajustar el visor de brackets
                    if (tabId === 'bracket') {
                        window.dispatchEvent(new Event('resize'));
                    }
                });
            });

            // Mejorar la visualización del bracket con hover en matches
            document.querySelectorAll('.bracket-match').forEach(match => {
                match.addEventListener('mouseenter', () => {
                    match.style.borderColor = 'var(--challonge-highlight)';
                    match.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
                });
                
                match.addEventListener('mouseleave', () => {
                    match.style.borderColor = 'var(--match-border)';
                    match.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
                });
            });

            // Animación para mostrar el ganador de un enfrentamiento
            document.querySelectorAll('.opponent-win').forEach(winner => {
                winner.style.transition = 'all 0.3s ease';
                winner.style.transform = 'scale(1.02)';
            });

            // Tooltip para cada editar button en la tabla para mejorar la UX
            document.querySelectorAll('button[data-enfrentamiento-id]').forEach(button => {
                button.addEventListener('mouseenter', function() {
                    const enfrentamientoData = JSON.parse(this.dataset.enfrentamiento);
                    const tooltip = document.createElement('div');
                    tooltip.className = 'absolute z-50 bg-gray-800 text-white text-xs p-2 rounded shadow-lg';
                    tooltip.style.bottom = 'calc(100% + 5px)';
                    tooltip.style.left = '50%';
                    tooltip.style.transform = 'translateX(-50%)';
                    tooltip.style.whiteSpace = 'nowrap';
                    tooltip.innerHTML = `
                        <div class="flex flex-col">
                            <div class="font-bold text-center mb-1">Editar Resultado</div>
                            <div class="flex justify-between gap-2">
                                <span>${enfrentamientoData.opponent1.nombre}</span>
                                <span>vs</span>
                                <span>${enfrentamientoData.opponent2.nombre}</span>
                            </div>
                        </div>
                    `;
                    
                    this.style.position = 'relative';
                    this.appendChild(tooltip);
                });
                
                button.addEventListener('mouseleave', function() {
                    const tooltip = this.querySelector('.bg-gray-800');
                    if (tooltip) {
                        tooltip.remove();
                    }
                });
            });

            // Mostrar un mensaje informativo para los usuarios
            function mostrarMensajeInfo(mensaje) {
                const mensajeElement = document.createElement('div');
                mensajeElement.className = 'fixed bottom-4 left-4 bg-blue-500 text-white px-4 py-2 rounded shadow-lg z-50 max-w-xs text-sm';
                mensajeElement.textContent = mensaje;
                document.body.appendChild(mensajeElement);
                
                setTimeout(() => {
                    mensajeElement.classList.add('opacity-0');
                    mensajeElement.style.transition = 'opacity 0.5s ease-out';
                    setTimeout(() => {
                        mensajeElement.remove();
                    }, 500);
                }, 3000);
            }
        });
    </script>
    @endpush

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/brackets-viewer@latest/dist/brackets-viewer.min.css" />
    <link rel="stylesheet" href="{{ asset('css/bracket.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bracket-dark.css') }}">
    
    <style>
        /* Estilos inspirados en Challonge */
        body {
            --challonge-primary: #FF7324;
            --challonge-secondary: #2A3990;
            --challonge-background: #F5F7FA;
            --challonge-text: #2D3748;
            --challonge-link: #3182CE;
            --challonge-border: #E2E8F0;
            --challonge-highlight: #FF7324;
            --challonge-winner: #48BB78;
            --challonge-loser: #F56565;
        }
        
        .dark body {
            --challonge-background: #1A202C;
            --challonge-text: #F7FAFC;
            --challonge-border: #2D3748;
            --challonge-link: #63B3ED;
            --challonge-highlight: #FF7324;
            --challonge-winner: #38A169;
            --challonge-loser: #E53E3E;
        }
        
        /* Mejoras para responsive */
        @media (max-width: 767px) {
            .challonge-header {
                padding: 1rem;
                margin-bottom: 1rem;
            }
            
            .challonge-header h1 {
                font-size: 1.25rem;
            }
            
            .challonge-header .flex {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .tabs-challonge {
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 0.25rem;
            }
            
            .brackets-viewer {
                padding: 0.5rem;
                min-height: 400px !important;
            }
            
            .tooltip {
                display: none; /* Ocultar tooltips en móvil para evitar problemas de espacio */
            }
            
            .toolbar-section {
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.5rem;
            }
            
            .toolbar-section .border-l {
                border-left: none;
                padding-left: 0;
                margin-top: 0.5rem;
                width: 100%;
                display: flex;
                justify-content: center;
            }
            
            .leyenda-colores {
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.75rem;
                padding: 0.5rem;
            }
            
            /* Reducir tamaño de texto en botones para móvil */
            .btn-challonge, .btn-challonge-secondary {
                padding: 0.35rem 0.5rem;
                font-size: 0.75rem;
            }
            
            .btn-challonge svg, .btn-challonge-secondary svg {
                height: 0.875rem;
                width: 0.875rem;
            }
            
            /* Mejorar visualización de tablas en móvil */
            .tabla-challonge {
                font-size: 0.75rem;
            }
            
            .tabla-challonge td, .tabla-challonge th {
                padding: 0.5rem;
            }
            
            /* Ocultar algunas columnas en móvil para ahorrar espacio */
            .tabla-challonge .hidden-mobile {
                display: none;
            }
            
            /* Modal más pequeño en móvil */
            #modal-resultado .bg-white {
                width: 90%;
                padding: 1rem;
            }
        }
        
        /* Tooltips para mejor UX */
        .tooltip-trigger {
            position: relative;
        }
        
        .tooltip {
            visibility: hidden;
            position: absolute;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #1A202C;
            color: white;
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.25rem;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s, visibility 0.3s;
            z-index: 10;
            pointer-events: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .tooltip::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 5px;
            border-style: solid;
            border-color: #1A202C transparent transparent transparent;
        }
        
        .tooltip-trigger:hover .tooltip {
            visibility: visible;
            opacity: 1;
        }
        
        /* Mostrar tooltips de ayuda en hover de campos de filtro */
        input:hover + .tooltip-help,
        select:hover + .tooltip-help {
            display: block !important;
        }
        
        /* Header con estilo Challonge */
        .challonge-header {
            background: linear-gradient(135deg, var(--challonge-primary), var(--challonge-secondary));
            color: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .challonge-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        /* Asegurar contraste en el header */
        .challonge-header .bg-white {
            color: white;
        }
        
        /* Mejorar apariencia de los brackets */
        .brackets-viewer {
            --bg-color: var(--challonge-background);
            --text-color: var(--challonge-text);
            --line-color: #94A3B8;
            --border-color: var(--challonge-border);
            --winner-bg: var(--challonge-winner);
            --winner-text: white;
            --loser-bg: var(--challonge-loser);
            --loser-text: white;
            --match-bg: white;
            --match-hover-bg: #F8FAFC;
            --match-border: var(--challonge-border);
            --match-highlight: var(--challonge-highlight);
            padding: 2rem;
            border-radius: 0.5rem;
        }
        
        .dark .brackets-viewer {
            --bg-color: var(--challonge-background);
            --text-color: var(--challonge-text);
            --line-color: #4A5568;
            --border-color: var(--challonge-border);
            --winner-bg: var(--challonge-winner);
            --winner-text: white;
            --loser-bg: var(--challonge-loser);
            --loser-text: white;
            --match-bg: #2D3748;
            --match-hover-bg: #1A365D;
            --match-border: var(--challonge-border);
            --match-highlight: var(--challonge-highlight);
        }
        
        .brackets-viewer .bracket-match {
            border: 1px solid var(--match-border);
            background-color: var(--match-bg);
            border-radius: 0.375rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        
        .brackets-viewer .bracket-match:hover {
            background-color: var(--match-hover-bg);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
            border-color: var(--challonge-highlight);
        }
        
        .brackets-viewer .opponent-win {
            background-color: var(--winner-bg);
            color: var(--winner-text);
            font-weight: bold;
        }
        
        .brackets-viewer .opponent-lose {
            background-color: var(--loser-bg);
            color: var(--loser-text);
        }
        
        /* Aumentar tamaño del texto para mejor legibilidad */
        .brackets-viewer .opponent-name {
            font-size: 0.9rem;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding: 0.5rem;
        }
        
        .brackets-viewer .opponent-score {
            font-weight: bold;
            font-size: 1rem;
        }
        
        /* Botones estilo Challonge */
        .btn-challonge {
            background-color: var(--challonge-primary);
            color: white;
            border-radius: 0.375rem;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
            padding: 0.5rem 1rem;
        }
        
        .btn-challonge:hover {
            background-color: #E65A00;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .btn-challonge-secondary {
            background-color: var(--challonge-secondary);
            color: white;
        }
        
        .btn-challonge-secondary:hover {
            background-color: #223276;
        }
        
        /* Mejoras para el modal */
        #modal-resultado {
            backdrop-filter: blur(4px);
        }
        
        #modal-resultado .bg-white {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border-radius: 0.5rem;
        }
        
        /* Tablas estilo Challonge */
        .tabla-challonge {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .tabla-challonge thead th {
            background-color: var(--challonge-primary);
            color: white;
            font-weight: 600;
            text-align: left;
            padding: 0.75rem 1rem;
        }
        
        .tabla-challonge tbody tr:nth-child(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .tabla-challonge tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .dark .tabla-challonge tbody tr:nth-child(odd) {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .dark .tabla-challonge tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .tabla-challonge td, .tabla-challonge th {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--challonge-border);
        }
        
        /* Pestañas/Tabs estilo Challonge */
        .tabs-challonge {
            display: flex;
            border-bottom: 1px solid var(--challonge-border);
            margin-bottom: 1rem;
        }
        
        .tab-challonge {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            color: var(--challonge-text);
            cursor: pointer;
            position: relative;
        }
        
        .tab-challonge.active {
            color: var(--challonge-primary);
        }
        
        .tab-challonge.active::after {
            content: "";
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--challonge-primary);
        }
        
        /* Contenedor principal con sombra */
        .bracket-container {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            overflow: hidden;
            position: relative;
        }
        
        /* Ocultar contenido de pestaña inactiva */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }

        /* Mejora de contraste para el texto */
        .btn-challonge, .btn-challonge-secondary {
            color: white !important;
        }

        .challonge-header .bg-white.bg-opacity-20 {
            color: white !important;
            font-weight: 500;
        }

        /* Mejorar contraste de texto en el encabezado */
        .challonge-header h1 {
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            font-weight: 700;
        }

        /* Asegurar contraste en las pestañas */
        .dark .tab-challonge:not(.active) {
            color: rgba(255, 255, 255, 0.8);
        }

        /* Mejorar contraste en los filtros */
        .dark select, .dark input[type="text"] {
            color: white;
        }

        /* Mejorar contraste en textos de ayuda */
        .dark .bg-blue-50.dark\:bg-blue-900\/20,
        .dark .bg-blue-50.dark\:bg-blue-900\/30 {
            color: white !important;
        }
        
        .dark .bg-blue-50.dark\:bg-blue-900\/20 .text-blue-800.dark\:text-blue-300,
        .dark .bg-blue-50.dark\:bg-blue-900\/30 .text-blue-800.dark\:text-white {
            color: white !important;
        }

        /* Mejoras para el texto en modo oscuro */
        .dark .text-gray-600 {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        /* Asegurar que los campos tengan texto blanco en modo oscuro */
        .dark .dark\:text-white {
            color: white !important;
        }

        /* Mejor contraste para modales en modo oscuro */
        .dark #modal-resultado .dark\:bg-gray-800 {
            color: white;
        }

        /* Asegurar que el texto del tutorial sea visible */
        .dark #tutorial-overlay .text-gray-600.dark\:text-gray-300 {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        /* Mejorar contraste de elementos interactivos */
        .dark .px-2.py-1.bg-gray-100.text-gray-600.rounded-full.text-xs.dark\:bg-gray-800.dark\:text-gray-400 {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        /* Mejorar contraste en la leyenda del bracket */
        .leyenda-colores .text-gray-800.dark\:text-white {
            color: white !important;
        }

        /* Mejorar contraste en tablas */
        .dark .tabla-challonge td {
            color: white !important;
        }

        /* Mejorar visibilidad de los estados */
        .dark .px-2.py-1.bg-green-100.text-green-800.rounded-full.text-xs.dark\:bg-green-800\/30.dark\:text-green-400,
        .dark .px-2.py-1.bg-green-100.text-green-800.rounded-full.text-xs.dark\:bg-green-800\/30.dark\:text-white {
            color: white !important;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
        }

        /* Asegurar que todos los textos en pestañas sean visibles */
        .dark .tab-challonge {
            color: white; 
        }

        /* Mejorar contraste en bracket matches */
        .dark .brackets-viewer .bracket-match {
            color: white !important;
        }
    </style>
    @endpush
</x-filament::page> 