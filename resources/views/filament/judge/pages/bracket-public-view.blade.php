<x-filament::page>
    @php
        $bracketData = $this->getBracketData();
        $llave = $bracketData['llave'];
        $datos = $bracketData['datos'];
    @endphp

    <!-- Meta tag CSRF para solicitudes POST -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="grid gap-6">
        <!-- Header con información del torneo -->
        <div class="challonge-header bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm">
            <h1 class="text-2xl font-bold dark:text-white">{{ $datos['torneo']['nombre'] }}</h1>
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
                        <span class="text-gray-900 dark:text-white">{{ count($datos['participantes']) }}</span>
                    </div>
                    <div class="bg-white bg-opacity-20 dark:bg-gray-700 rounded px-2 py-1">
                        <span class="font-semibold text-gray-900 dark:text-white">Completados:</span>
                        <span class="text-gray-900 dark:text-white">{{ $llave->enfrentamientos()->whereNotNull('ganador_id')->count() }}</span>
                    </div>
                    <div class="bg-white bg-opacity-20 dark:bg-gray-700 rounded px-2 py-1">
                        <span class="font-semibold text-gray-900 dark:text-white">Pendientes:</span>
                        <span class="text-gray-900 dark:text-white">{{ $llave->enfrentamientos()->whereNull('ganador_id')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Visualizador de bracket -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h2 class="text-lg font-semibold dark:text-white">Bracket del Torneo</h2>
                        <div class="flex gap-2">
                            <input 
                                type="text" 
                                id="buscar-equipo" 
                                placeholder="Buscar equipo..." 
                                class="text-sm px-3 py-1.5 border border-gray-300 rounded-md focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                            <button id="zoom-in" class="p-2 rounded-md bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-800/20 dark:text-amber-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button id="zoom-out" class="p-2 rounded-md bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-800/20 dark:text-amber-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button id="zoom-reset" class="p-2 rounded-md bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-800/20 dark:text-amber-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Leyenda para el bracket -->
                    <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700 flex flex-wrap gap-3 text-xs">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-1"></div>
                            <span class="dark:text-white">Completado</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-amber-500 rounded-full mr-1"></div>
                            <span class="dark:text-white">En curso</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-gray-300 dark:bg-gray-500 rounded-full mr-1"></div>
                            <span class="dark:text-white">Pendiente</span>
                        </div>
                        <div class="flex items-center ml-auto">
                            <span class="italic text-gray-500 dark:text-gray-400">Haga click en un enfrentamiento para ver detalles</span>
                        </div>
                    </div>
                    
                    <div class="p-4 overflow-x-auto">
                        <div id="bracket-container" class="min-h-[500px]"></div>
                    </div>
                </div>
                
                <!-- Estadísticas del torneo -->
                <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                    <h3 class="text-lg font-semibold mb-3 dark:text-white">Estadísticas del Torneo</h3>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center">
                            <div class="text-sm text-blue-600 dark:text-blue-400 mb-1">Total Partidas</div>
                            <div class="text-2xl font-bold text-blue-800 dark:text-blue-300">{{ $llave->enfrentamientos()->count() }}</div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center">
                            <div class="text-sm text-green-600 dark:text-green-400 mb-1">Completadas</div>
                            <div class="text-2xl font-bold text-green-800 dark:text-green-300">{{ $llave->enfrentamientos()->whereNotNull('ganador_id')->count() }}</div>
                        </div>
                        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 text-center">
                            <div class="text-sm text-amber-600 dark:text-amber-400 mb-1">Pendientes</div>
                            <div class="text-2xl font-bold text-amber-800 dark:text-amber-300">{{ $llave->enfrentamientos()->whereNull('ganador_id')->count() }}</div>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 text-center">
                            <div class="text-sm text-purple-600 dark:text-purple-400 mb-1">Equipos</div>
                            <div class="text-2xl font-bold text-purple-800 dark:text-purple-300">{{ count($datos['participantes']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de próximos enfrentamientos -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold dark:text-white">Próximos Enfrentamientos</h2>
                    </div>
                    <div class="p-4">
                        @php
                            $proximosEnfrentamientos = $this->getProximosEnfrentamientos($llave, 5);
                        @endphp

                        @if($proximosEnfrentamientos->count() > 0)
                            <div class="space-y-4">
                                @foreach($proximosEnfrentamientos as $enfrentamiento)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Ronda {{ $enfrentamiento->ronda }}</span>
                                        <a href="{{ route('filament.judge.pages.bracket-admin.{id}', ['id' => $llave->id]) }}" 
                                           class="text-xs text-amber-600 hover:text-amber-700 dark:text-amber-400">
                                            Registrar resultado →
                                        </a>
                                    </div>
                                    <div class="grid grid-cols-5 gap-2 items-center">
                                        <div class="col-span-2 text-right">
                                            <span class="font-medium dark:text-white">{{ $enfrentamiento->equipo1->nombre }}</span>
                                        </div>
                                        <div class="col-span-1 text-center font-bold dark:text-white">VS</div>
                                        <div class="col-span-2">
                                            <span class="font-medium dark:text-white">{{ $enfrentamiento->equipo2->nombre }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                                No hay enfrentamientos pendientes.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Panel de últimos resultados -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm mt-6">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold dark:text-white">Últimos Resultados</h2>
                    </div>
                    <div class="p-4">
                        @php
                            $ultimosResultados = $this->getUltimosResultados($llave, 5);
                        @endphp

                        @if($ultimosResultados->count() > 0)
                            <div class="space-y-4">
                                @foreach($ultimosResultados as $enfrentamiento)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Ronda {{ $enfrentamiento->ronda }}</span>
                                        <span class="text-xs font-medium text-green-500">Completado</span>
                                    </div>
                                    <div class="grid grid-cols-5 gap-2 items-center">
                                        <div class="col-span-2 text-right">
                                            <span class="font-medium dark:text-white 
                                                  {{ $enfrentamiento->ganador_id == $enfrentamiento->equipo1_id ? 'text-green-600 dark:text-green-400 font-bold' : '' }}">
                                                {{ $enfrentamiento->equipo1->nombre }}
                                            </span>
                                            <span class="ml-2 font-bold">{{ $enfrentamiento->puntaje_equipo1 }}</span>
                                        </div>
                                        <div class="col-span-1 text-center text-xs font-medium dark:text-white">-</div>
                                        <div class="col-span-2">
                                            <span class="font-bold">{{ $enfrentamiento->puntaje_equipo2 }}</span>
                                            <span class="ml-2 font-medium dark:text-white
                                                  {{ $enfrentamiento->ganador_id == $enfrentamiento->equipo2_id ? 'text-green-600 dark:text-green-400 font-bold' : '' }}">
                                                {{ $enfrentamiento->equipo2->nombre }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                                Aún no hay resultados registrados.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de todos los enfrentamientos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold dark:text-white">Todos los Enfrentamientos</h2>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <th class="px-4 py-2 border dark:border-gray-600">Ronda</th>
                            <th class="px-4 py-2 border dark:border-gray-600">Equipo 1</th>
                            <th class="px-4 py-2 border dark:border-gray-600">Puntaje</th>
                            <th class="px-4 py-2 border dark:border-gray-600">Equipo 2</th>
                            <th class="px-4 py-2 border dark:border-gray-600">Puntaje</th>
                            <th class="px-4 py-2 border dark:border-gray-600">Ganador</th>
                            <th class="px-4 py-2 border dark:border-gray-600">Estado</th>
                            <th class="px-4 py-2 border dark:border-gray-600">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($llave->enfrentamientos()->with(['equipo1', 'equipo2', 'ganador'])->orderBy('ronda')->orderBy('posicion')->get() as $enfrentamiento)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700
                            @if($enfrentamiento->ganador_id) bg-green-50 dark:bg-green-900/10 @endif">
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
                                @if ($enfrentamiento->tieneResultado())
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs dark:bg-green-800/30 dark:text-green-400">Completado</span>
                                @else
                                    <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-full text-xs dark:bg-amber-800/30 dark:text-amber-400">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 border dark:border-gray-600">
                                @if ($enfrentamiento->equipo1_id && $enfrentamiento->equipo2_id)
                                <a href="{{ route('filament.judge.pages.bracket-admin.{id}', ['id' => $llave->id]) }}" 
                                    class="px-2 py-1 bg-amber-600 text-white rounded hover:bg-amber-700 text-xs">
                                    {{ $enfrentamiento->tieneResultado() ? 'Editar' : 'Registrar' }}
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Datos para el bracket
            const bracketData = @json($datos);
            
            // Aquí iría la lógica para inicializar el visualizador de bracket
            // (Se asume que se está usando alguna biblioteca como brackets-viewer.js)
            console.log('Datos del bracket cargados:', bracketData);
            
            // Implementación simple para los botones de zoom
            let scale = 1;
            const bracketContainer = document.getElementById('bracket-container');
            
            document.getElementById('zoom-in').addEventListener('click', function() {
                scale += 0.1;
                bracketContainer.style.transform = `scale(${scale})`;
                bracketContainer.style.transformOrigin = 'top left';
            });
            
            document.getElementById('zoom-out').addEventListener('click', function() {
                if (scale > 0.5) scale -= 0.1;
                bracketContainer.style.transform = `scale(${scale})`;
                bracketContainer.style.transformOrigin = 'top left';
            });
            
            document.getElementById('zoom-reset').addEventListener('click', function() {
                scale = 1;
                bracketContainer.style.transform = `scale(${scale})`;
            });

            // Funcionalidad de búsqueda de equipos
            const buscarEquipo = document.getElementById('buscar-equipo');
            if (buscarEquipo) {
                buscarEquipo.addEventListener('input', function(e) {
                    const searchText = e.target.value.toLowerCase().trim();
                    
                    // Si el campo está vacío, resetear todas las opacidades
                    if (searchText === '') {
                        document.querySelectorAll('.brackets-viewer .match').forEach(match => {
                            match.style.opacity = '1';
                            match.style.filter = 'none';
                        });
                        return;
                    }
                    
                    // Buscar en todos los partidos
                    let encontrado = false;
                    document.querySelectorAll('.brackets-viewer .match').forEach(match => {
                        const equipoNombres = match.querySelectorAll('.opponent-name');
                        let matchContainsText = false;
                        
                        equipoNombres.forEach(nombre => {
                            if (nombre.textContent.toLowerCase().includes(searchText)) {
                                matchContainsText = true;
                                encontrado = true;
                            }
                        });
                        
                        // Resaltar los que coinciden, atenuar los que no
                        if (matchContainsText) {
                            match.style.opacity = '1';
                            match.style.filter = 'drop-shadow(0 0 8px rgba(245, 158, 11, 0.5))';
                            match.style.zIndex = '10';
                        } else {
                            match.style.opacity = '0.3';
                            match.style.filter = 'grayscale(1)';
                            match.style.zIndex = '0';
                        }
                    });
                    
                    // Notificar si no se encontró nada
                    if (!encontrado && searchText.length > 2) {
                        // Usar Filament notification si está disponible
                        if (typeof $dispatch === 'function') {
                            $dispatch('notification', {
                                title: 'Sin resultados',
                                body: 'No se encontraron equipos con ese nombre.',
                                status: 'info',
                                timeout: 3000
                            });
                        }
                    }
                });
            }
        });
    </script>
</x-filament::page> 