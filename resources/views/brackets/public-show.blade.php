@extends('layouts.filament-wrapper')

@section('title', 'Bracket: ' . $llave->categoriaEvento->categoria->nombre)

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/brackets-viewer@latest/dist/brackets-viewer.min.css" />
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="grid gap-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <div>
                <span class="font-medium dark:text-white">Tipo de torneo:</span> 
                @switch($llave->tipo_fixture)
                    @case('eliminacion_directa')
                        <span class="dark:text-white">Eliminación Directa (Single Elimination)</span>
                        @break
                    @case('eliminacion_doble')
                        <span class="dark:text-white">Eliminación Doble (Double Elimination)</span>
                        @break
                    @case('todos_contra_todos')
                        <span class="dark:text-white">Todos contra Todos (Round Robin)</span>
                        @break
                    @case('suizo')
                        <span class="dark:text-white">Sistema Suizo (Swiss)</span>
                        @break
                    @case('grupos')
                        <span class="dark:text-white">Fase de Grupos</span>
                        @break
                    @case('fase_grupos_eliminacion')
                        <span class="dark:text-white">Fase de Grupos + Eliminación</span>
                        @break
                    @default
                        <span class="dark:text-white">{{ $llave->tipo_fixture }}</span>
                @endswitch
            </div>
            
            <div>
                <span class="font-medium dark:text-white">Estado:</span>
                <span class="px-2 py-1 rounded-full text-sm 
                    @if($llave->estado_torneo == 'pendiente') bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-white
                    @elseif($llave->estado_torneo == 'en_curso') bg-green-200 text-green-800 dark:bg-green-700 dark:text-white
                    @elseif($llave->estado_torneo == 'pausado') bg-yellow-200 text-yellow-800 dark:bg-yellow-700 dark:text-white
                    @elseif($llave->estado_torneo == 'finalizado') bg-red-200 text-red-800 dark:bg-red-700 dark:text-white
                    @endif">
                    {{ ucfirst($llave->estado_torneo) }}
                </span>
            </div>
        </div>
        
        <div class="stats flex flex-wrap gap-4 text-sm">
            <div class="stat p-2 bg-gray-100 dark:bg-gray-700 rounded">
                <div class="stat-title text-gray-700 dark:text-gray-300">Equipos</div>
                <div class="stat-value text-xl text-gray-900 dark:text-white">{{ $llave->estructura['total_equipos'] ?? 0 }}</div>
            </div>
            
            <div class="stat p-2 bg-gray-100 dark:bg-gray-700 rounded">
                <div class="stat-title text-gray-700 dark:text-gray-300">Rondas</div>
                <div class="stat-value text-xl text-gray-900 dark:text-white">{{ $llave->estructura['total_rondas'] ?? 0 }}</div>
            </div>
            
            <div class="stat p-2 bg-gray-100 dark:bg-gray-700 rounded">
                <div class="stat-title text-gray-700 dark:text-gray-300">Enfrentamientos</div>
                <div class="stat-value text-xl text-gray-900 dark:text-white">{{ $llave->enfrentamientos()->count() }}</div>
            </div>
            
            <div class="stat p-2 bg-gray-100 dark:bg-gray-700 rounded">
                <div class="stat-title text-gray-700 dark:text-gray-300">Completados</div>
                <div class="stat-value text-xl text-gray-900 dark:text-white">{{ $llave->enfrentamientos()->whereNotNull('ganador_id')->count() }}</div>
            </div>
        </div>
    </div>
    
    <!-- Visor de Bracket (estilo Challonge) -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm overflow-auto">
        <div id="brackets-viewer" class="brackets-viewer w-full min-h-[600px]"></div>
    </div>
</div>

<!-- Modal para Editar Resultado -->
<div id="modal-resultado" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md mx-auto">
        <h3 class="text-xl font-bold mb-4 dark:text-white">Actualizar Resultado</h3>
        
        <form id="form-resultado" class="space-y-4">
            <input type="hidden" name="enfrentamiento_id" id="enfrentamiento_id">
            
            <div class="grid grid-cols-5 gap-2 items-center">
                <div class="col-span-2">
                    <span id="equipo1-nombre" class="font-semibold dark:text-white"></span>
                </div>
                
                <div class="col-span-1 text-center dark:text-white">
                    VS
                </div>
                
                <div class="col-span-2 text-right">
                    <span id="equipo2-nombre" class="font-semibold dark:text-white"></span>
                </div>
                
                <div class="col-span-2">
                    <input type="number" name="puntaje_equipo1" id="puntaje_equipo1" 
                        class="w-full border rounded p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" min="0" required>
                </div>
                
                <div class="col-span-1 text-center dark:text-white">
                    -
                </div>
                
                <div class="col-span-2">
                    <input type="number" name="puntaje_equipo2" id="puntaje_equipo2" 
                        class="w-full border rounded p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" min="0" required>
                </div>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button type="button" id="btn-cancelar" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 rounded dark:text-white">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<!-- Necesitamos JQuery y la biblioteca para visualización de brackets -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/brackets-viewer@latest/dist/brackets-viewer.min.js"></script>

<!-- Bibliotecas para exportación -->
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
    // Asegurarse de que jspdf esté disponible globalmente
    window.jspdf = window.jspdf || { jsPDF: window.jsPDF };
    
    // Exponer los datos del bracket a través de una variable global
    window.bracketData = @json($datos);
    
    // Configurar el manejo del formulario de resultado
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modal-resultado');
        const form = document.getElementById('form-resultado');
        const btnCancelar = document.getElementById('btn-cancelar');
        
        btnCancelar.addEventListener('click', function() {
            modal.classList.add('hidden');
        });
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const enfrentamientoId = document.getElementById('enfrentamiento_id').value;
            const puntaje1 = document.getElementById('puntaje_equipo1').value;
            const puntaje2 = document.getElementById('puntaje_equipo2').value;
            
            // Determinar ganador automáticamente
            let ganadorId = null;
            
            if (parseInt(puntaje1) > parseInt(puntaje2)) {
                // Buscar el equipo1_id para este enfrentamiento
                const enfrentamiento = window.bracketData.matches.find(m => m.id == enfrentamientoId);
                ganadorId = enfrentamiento.equipo1_id;
            } else if (parseInt(puntaje2) > parseInt(puntaje1)) {
                // Buscar el equipo2_id para este enfrentamiento
                const enfrentamiento = window.bracketData.matches.find(m => m.id == enfrentamientoId);
                ganadorId = enfrentamiento.equipo2_id;
            }
            
            // Mostrar indicador de carga
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Guardando...';
            
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
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al guardar el resultado');
                }
                return response.json();
            })
            .then(data => {
                console.log('Resultado guardado:', data);
                // Cerrar el modal
                modal.classList.add('hidden');
                
                // Recargar la página para mostrar los cambios
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al guardar el resultado. Inténtalo de nuevo.');
            })
            .finally(() => {
                // Restaurar botón
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
    });
</script>

<!-- Incluir nuestro script personalizado para el bracket -->
<script src="{{ asset('js/bracket/bracket-viewer.js') }}"></script>
@endsection 