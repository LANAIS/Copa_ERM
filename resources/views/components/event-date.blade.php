@props([
    'numero' => '1',
    'localidad' => 'Localidad',
    'fecha' => '01/01/2025',
    'categorias' => [],
])

<div {{ $attributes->merge(['class' => 'fecha-evento']) }}>
    <div class="fecha-content">
        <!-- Cabecera con el número de fecha -->
        <div class="fecha-header {{ str_contains(strtolower($numero), 'final') ? 'fecha-final' : '' }}">
            <div class="fecha-numero">
                {{ str_contains(strtolower($numero), 'final') ? 'FINAL' : "FECHA $numero" }}
            </div>
        </div>
        
        <!-- Contenido principal -->
        <div class="fecha-body">
            <!-- Título de la localidad -->
            <h3 class="fecha-localidad">{{ $localidad }}</h3>
            
            <!-- Fecha y ubicación -->
            <div class="fecha-detalles">
                <div class="fecha-info">
                    <i class="fas fa-calendar-alt"></i>
                    <span>{{ $fecha }}</span>
                </div>
                
                <div class="fecha-lugar">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Ver ubicación</span>
                </div>
            </div>
            
            <!-- Categorías -->
            @if(count($categorias) > 0)
                <div class="fecha-categorias">
                    <div class="categorias-titulo">
                        <i class="fas fa-trophy"></i>
                        <span>CATEGORÍAS</span>
                    </div>
                    
                    <div class="categorias-lista">
                        @foreach($categorias as $categoria)
                        <span class="categoria-tag">
                            {{ $categoria }}
                        </span>
                        @endforeach
                    </div>
                    
                    <!-- Botón para más información -->
                    <button class="fecha-boton">
                        <i class="fas fa-info-circle"></i>
                        Más información
                    </button>
                </div>
            @endif
        </div>
    </div>
</div> 