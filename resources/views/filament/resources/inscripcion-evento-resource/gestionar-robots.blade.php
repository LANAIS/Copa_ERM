<div class="p-4">
    <h2 class="text-xl font-bold mb-4">Gestión de Robots Participantes</h2>
    
    @if($robots->isEmpty())
        <div class="p-4 bg-yellow-50 rounded-lg text-yellow-800">
            No hay robots asociados a esta inscripción.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Robot</th>
                        <th scope="col" class="px-6 py-3">Modalidad</th>
                        <th scope="col" class="px-6 py-3">Participante</th>
                        <th scope="col" class="px-6 py-3">Homologado</th>
                        <th scope="col" class="px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($robots as $robot)
                        <tr class="bg-white border-b">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $robot->nombre }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $robot->modalidad }}
                            </td>
                            <td class="px-6 py-4">
                                @if($robot->participante)
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Sí</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($robot->homologado)
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Sí</span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    @if(!$robot->participante)
                                        <button 
                                            onclick="toggleParticipacion('{{ $inscripcion->id }}', '{{ $robot->id }}', true)"
                                            class="px-3 py-1 bg-blue-500 text-white rounded-md text-xs">
                                            Incluir
                                        </button>
                                    @else
                                        <button 
                                            onclick="toggleParticipacion('{{ $inscripcion->id }}', '{{ $robot->id }}', false)"
                                            class="px-3 py-1 bg-gray-300 text-gray-700 rounded-md text-xs">
                                            Excluir
                                        </button>
                                        
                                        @if(!$robot->homologado)
                                            <button 
                                                onclick="homologarRobot('{{ $inscripcion->id }}', '{{ $robot->id }}')"
                                                class="px-3 py-1 bg-green-500 text-white rounded-md text-xs">
                                                Homologar
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<script>
    function toggleParticipacion(inscripcionId, robotId, participante) {
        Livewire.dispatch('toggleRobotParticipante', {
            inscripcionId: inscripcionId, 
            robotId: robotId, 
            participante: participante
        });
    }
    
    function homologarRobot(inscripcionId, robotId) {
        Livewire.dispatch('homologarRobot', {
            inscripcionId: inscripcionId, 
            robotId: robotId
        });
    }
</script> 