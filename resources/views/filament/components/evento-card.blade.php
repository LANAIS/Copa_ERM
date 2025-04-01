<div class="prose max-w-none">
    <h3 class="text-xl font-bold">{{ $evento->nombre }}</h3>
    
    @if($evento->banner)
        <img src="{{ asset('storage/'.$evento->banner) }}" alt="{{ $evento->nombre }}" class="rounded-lg max-h-32 object-cover mb-4">
    @endif
    
    <div class="space-y-2">
        <div class="flex items-start gap-2">
            <span class="font-semibold text-primary-600">Lugar:</span> 
            <span class="text-gray-800 dark:text-white">{{ $evento->lugar ?: 'No especificado' }}</span>
        </div>
        
        <div class="flex items-start gap-2">
            <span class="font-semibold text-primary-600">Inicio:</span> 
            <span class="text-gray-800 dark:text-white">{{ $evento->fecha_inicio->format('d/m/Y H:i') }}</span>
        </div>
        
        <div class="flex items-start gap-2">
            <span class="font-semibold text-primary-600">Fin:</span> 
            <span class="text-gray-800 dark:text-white">{{ $evento->fecha_fin->format('d/m/Y H:i') }}</span>
        </div>
        
        <div class="flex items-start gap-2">
            <span class="font-semibold text-primary-600">Inscripciones abiertas:</span>
            <span class="text-gray-800 dark:text-white">{{ $evento->inicio_inscripciones->format('d/m/Y H:i') }} - {{ $evento->fin_inscripciones->format('d/m/Y H:i') }}</span>
        </div>
        
        <div class="flex items-start gap-2">
            <span class="font-semibold text-primary-600">Estado:</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                @if($evento->estado === 'abierto') bg-green-100 text-green-800
                @elseif($evento->estado === 'cerrado') bg-yellow-100 text-yellow-800
                @elseif($evento->estado === 'finalizado') bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ ucfirst($evento->estado) }}
            </span>
        </div>
        
        @if($evento->descripcion)
            <div>
                <p class="font-semibold text-primary-600">Descripción:</p>
                <p class="text-gray-800 dark:text-white">{{ $evento->descripcion }}</p>
            </div>
        @endif
    </div>
</div> 