@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="text-2xl font-bold mb-4">{{ $llave->categoriaEvento->categoria->nombre }}</h1>
    
    <div class="mb-4 bg-white shadow rounded-lg p-4">
        <div class="flex justify-between items-center mb-4">
            <div>
                <span class="font-medium">Tipo de torneo:</span> 
                @switch($llave->tipo_fixture)
                    @case('eliminacion_directa')
                        Eliminación Directa (Single Elimination)
                        @break
                    @case('eliminacion_doble')
                        Eliminación Doble (Double Elimination)
                        @break
                    @case('todos_contra_todos')
                        Todos contra Todos (Round Robin)
                        @break
                    @case('suizo')
                        Sistema Suizo (Swiss)
                        @break
                    @case('grupos')
                        Fase de Grupos
                        @break
                    @case('fase_grupos_eliminacion')
                        Fase de Grupos + Eliminación
                        @break
                    @default
                        {{ $llave->tipo_fixture }}
                @endswitch
            </div>
            
            <div>
                <span class="font-medium">Estado:</span>
                <span class="px-2 py-1 rounded-full text-sm 
                    @if($llave->estado_torneo == 'pendiente') bg-gray-200 text-gray-800
                    @elseif($llave->estado_torneo == 'en_curso') bg-green-200 text-green-800
                    @elseif($llave->estado_torneo == 'pausado') bg-yellow-200 text-yellow-800
                    @elseif($llave->estado_torneo == 'finalizado') bg-red-200 text-red-800
                    @endif">
                    {{ ucfirst($llave->estado_torneo) }}
                </span>
            </div>
        </div>
        
        <div class="stats flex flex-wrap gap-4 text-sm">
            <div class="stat p-2 bg-gray-100 rounded">
                <div class="stat-title">Equipos</div>
                <div class="stat-value text-xl">{{ $llave->estructura['total_equipos'] ?? 0 }}</div>
            </div>
            
            <div class="stat p-2 bg-gray-100 rounded">
                <div class="stat-title">Rondas</div>
                <div class="stat-value text-xl">{{ $llave->estructura['total_rondas'] ?? 0 }}</div>
            </div>
            
            <div class="stat p-2 bg-gray-100 rounded">
                <div class="stat-title">Enfrentamientos</div>
                <div class="stat-value text-xl">{{ $llave->enfrentamientos()->count() }}</div>
            </div>
            
            <div class="stat p-2 bg-gray-100 rounded">
                <div class="stat-title">Completados</div>
                <div class="stat-value text-xl">{{ $llave->enfrentamientos()->whereNotNull('ganador_id')->count() }}</div>
            </div>
        </div>
    </div>
    
    <!-- Visor de Bracket (estilo Challonge) -->
    <div class="brackets-container mb-6 bg-white rounded-lg p-4 overflow-auto shadow">
        <div id="brackets-viewer" class="brackets-viewer w-full min-h-[600px]"></div>
    </div>
    
    <!-- Modal para Editar Resultado -->
    <div id="modal-resultado" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-md mx-auto">
            <h3 class="text-xl font-bold mb-4">Actualizar Resultado</h3>
            
            <form id="form-resultado" class="space-y-4">
                <input type="hidden" name="enfrentamiento_id" id="enfrentamiento_id">
                
                <div class="grid grid-cols-5 gap-2 items-center">
                    <div class="col-span-2">
                        <span id="equipo1-nombre" class="font-semibold"></span>
                    </div>
                    
                    <div class="col-span-1 text-center">
                        VS
                    </div>
                    
                    <div class="col-span-2 text-right">
                        <span id="equipo2-nombre" class="font-semibold"></span>
                    </div>
                    
                    <div class="col-span-2">
                        <input type="number" name="puntaje_equipo1" id="puntaje_equipo1" 
                            class="w-full border rounded p-2" min="0" required>
                    </div>
                    
                    <div class="col-span-1 text-center">
                        -
                    </div>
                    
                    <div class="col-span-2">
                        <input type="number" name="puntaje_equipo2" id="puntaje_equipo2" 
                            class="w-full border rounded p-2" min="0" required>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" id="btn-cancelar" class="px-4 py-2 bg-gray-300 rounded">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Necesitamos JQuery y la biblioteca para visualización de brackets -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/brackets-viewer@latest/dist/brackets-viewer.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/brackets-viewer@latest/dist/brackets-viewer.min.css" />

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Datos del bracket
        const datos = @json($datos);
        
        // Renderizar el bracket usando brackets-viewer
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
                    result: match.ganador_id === match.equipo1_id ? 'win' : 
                           (match.ganador_id && match.ganador_id !== match.equipo1_id ? 'loss' : null)
                },
                opponent2: {
                    id: match.equipo2_id,
                    score: match.puntaje_equipo2,
                    result: match.ganador_id === match.equipo2_id ? 'win' : 
                           (match.ganador_id && match.ganador_id !== match.equipo2_id ? 'loss' : null)
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
        
        // Función para convertir el tipo de torneo al formato esperado por brackets-viewer
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
        
        // Manejo del modal para editar resultados
        const modal = document.getElementById('modal-resultado');
        const form = document.getElementById('form-resultado');
        const btnCancelar = document.getElementById('btn-cancelar');
        
        btnCancelar.addEventListener('click', function() {
            modal.classList.add('hidden');
        });
        
        function abrirModalResultado(match) {
            // Solo permitir editar si ambos equipos están asignados
            if (!match.opponent1.id || !match.opponent2.id) {
                return;
            }
            
            // Buscar los nombres de los equipos
            const equipo1 = datos.participantes.find(p => p.id === match.opponent1.id);
            const equipo2 = datos.participantes.find(p => p.id === match.opponent2.id);
            
            document.getElementById('enfrentamiento_id').value = match.id;
            document.getElementById('equipo1-nombre').textContent = equipo1 ? equipo1.nombre : 'Equipo 1';
            document.getElementById('equipo2-nombre').textContent = equipo2 ? equipo2.nombre : 'Equipo 2';
            document.getElementById('puntaje_equipo1').value = match.opponent1.score || 0;
            document.getElementById('puntaje_equipo2').value = match.opponent2.score || 0;
            
            modal.classList.remove('hidden');
        }
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const enfrentamientoId = document.getElementById('enfrentamiento_id').value;
            const puntaje1 = document.getElementById('puntaje_equipo1').value;
            const puntaje2 = document.getElementById('puntaje_equipo2').value;
            
            // Determinar ganador automáticamente
            let ganadorId = null;
            
            if (parseInt(puntaje1) > parseInt(puntaje2)) {
                // Buscar el equipo1_id para este enfrentamiento
                const enfrentamiento = datos.matches.find(m => m.id == enfrentamientoId);
                ganadorId = enfrentamiento.equipo1_id;
            } else if (parseInt(puntaje2) > parseInt(puntaje1)) {
                // Buscar el equipo2_id para este enfrentamiento
                const enfrentamiento = datos.matches.find(m => m.id == enfrentamientoId);
                ganadorId = enfrentamiento.equipo2_id;
            }
            
            // Enviar al backend
            fetch(`/api/enfrentamientos/${enfrentamientoId}/resultado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    puntaje_equipo1: puntaje1,
                    puntaje_equipo2: puntaje2,
                    ganador_id: ganadorId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cerrar modal y recargar para ver cambios
                    modal.classList.add('hidden');
                    window.location.reload();
                } else {
                    alert('Error al guardar el resultado');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Hubo un error al procesar la solicitud');
            });
        });
    });
</script>
@endsection 