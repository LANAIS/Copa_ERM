@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="text-2xl font-bold mb-4">Administrar Bracket: {{ $llave->categoriaEvento->categoria->nombre }}</h1>
    
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Configuración del Tipo de Torneo -->
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-3">Configuración del Torneo</h2>
            
            <form action="{{ route('admin.brackets.configurar_tipo', $llave->id) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="tipo_fixture" class="block text-sm font-medium mb-1">Tipo de Torneo</label>
                    <select id="tipo_fixture" name="tipo_fixture" class="w-full border-gray-300 rounded-md shadow-sm">
                        @foreach ($tiposTorneo as $valor => $nombre)
                            <option value="{{ $valor }}" {{ $llave->tipo_fixture == $valor ? 'selected' : '' }}>
                                {{ $nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="usar_cabezas_serie" value="1" 
                               {{ $llave->usar_cabezas_serie ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm">
                        <span class="text-sm">Usar cabezas de serie (seeding)</span>
                    </label>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                    Guardar Configuración
                </button>
            </form>
        </div>
        
        <!-- Generación del Bracket -->
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-3">Generar Bracket</h2>
            
            @if ($llave->estado_torneo === 'pendiente')
                <form action="{{ route('admin.brackets.generar', $llave->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Equipos Participantes</label>
                        <div class="max-h-48 overflow-y-auto border border-gray-300 rounded-md p-2">
                            @foreach ($equipos as $equipo)
                                <label class="flex items-center space-x-3 py-1">
                                    <input type="checkbox" name="equipos[]" value="{{ $equipo->id }}" 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm">
                                    <span>{{ $equipo->nombre }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Opciones específicas según el tipo de torneo -->
                    <div id="opciones-suizo" class="mb-4 {{ $llave->tipo_fixture === 'suizo' ? '' : 'hidden' }}">
                        <label for="rondas" class="block text-sm font-medium mb-1">Número de Rondas</label>
                        <input type="number" id="rondas" name="rondas" min="2" max="10" value="3"
                               class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    
                    <div id="opciones-grupos" class="mb-4 {{ in_array($llave->tipo_fixture, ['grupos', 'fase_grupos_eliminacion']) ? '' : 'hidden' }}">
                        <label for="num_grupos" class="block text-sm font-medium mb-1">Número de Grupos</label>
                        <input type="number" id="num_grupos" name="num_grupos" min="2" max="8" value="2"
                               class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    
                    <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">
                        Generar Bracket
                    </button>
                </form>
            @else
                <div class="p-4 bg-yellow-50 text-yellow-700 rounded-md">
                    El torneo ya ha sido iniciado. No se puede generar un nuevo bracket.
                </div>
            @endif
        </div>
        
        <!-- Control del Torneo -->
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-3">Control del Torneo</h2>
            
            <div class="mb-4">
                <div class="py-2 px-3 bg-gray-100 rounded-md mb-2">
                    <span class="font-medium">Estado actual:</span>
                    <span class="px-2 py-1 rounded-full text-sm 
                        @if($llave->estado_torneo == 'pendiente') bg-gray-200 text-gray-800
                        @elseif($llave->estado_torneo == 'en_curso') bg-green-200 text-green-800
                        @elseif($llave->estado_torneo == 'pausado') bg-yellow-200 text-yellow-800
                        @elseif($llave->estado_torneo == 'finalizado') bg-red-200 text-red-800
                        @endif">
                        {{ ucfirst($llave->estado_torneo) }}
                    </span>
                </div>
                
                <div class="flex flex-col space-y-2">
                    @if ($llave->estado_torneo === 'pendiente')
                        <form action="{{ route('admin.brackets.iniciar', $llave->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">
                                Iniciar Torneo
                            </button>
                        </form>
                    @endif
                    
                    @if ($llave->estado_torneo === 'en_curso')
                        <form action="{{ route('admin.brackets.finalizar', $llave->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700">
                                Finalizar Torneo
                            </button>
                        </form>
                    @endif
                    
                    @if (in_array($llave->estado_torneo, ['en_curso', 'finalizado']))
                        <form action="{{ route('admin.brackets.reiniciar', $llave->id) }}" method="POST" 
                              onsubmit="return confirm('¿Está seguro de reiniciar el torneo? Se eliminarán todos los resultados.')">
                            @csrf
                            <button type="submit" class="w-full bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700">
                                Reiniciar Torneo
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('brackets.show', $llave->id) }}" 
                       class="block text-center bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                        Ver Bracket
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipoFixtureSelect = document.getElementById('tipo_fixture');
        const opcionesSuizo = document.getElementById('opciones-suizo');
        const opcionesGrupos = document.getElementById('opciones-grupos');
        
        tipoFixtureSelect.addEventListener('change', function() {
            if (this.value === 'suizo') {
                opcionesSuizo.classList.remove('hidden');
            } else {
                opcionesSuizo.classList.add('hidden');
            }
            
            if (this.value === 'grupos' || this.value === 'fase_grupos_eliminacion') {
                opcionesGrupos.classList.remove('hidden');
            } else {
                opcionesGrupos.classList.add('hidden');
            }
        });
    });
</script>
@endsection 