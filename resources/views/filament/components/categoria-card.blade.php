<div class="prose max-w-none">
    <h3 class="text-xl font-bold">{{ $categoriaEvento->categoria->nombre }}</h3>
    
    <div class="space-y-2">
        <div class="flex items-start gap-2">
            <span class="font-semibold text-primary-600">Participantes:</span> 
            <span class="text-gray-800 dark:text-white">{{ $categoriaEvento->participantes_min }} - {{ $categoriaEvento->participantes_max }}</span>
        </div>
        
        <div class="flex items-start gap-2">
            <span class="font-semibold text-primary-600">Cupo disponible:</span> 
            <span class="text-gray-800 dark:text-white">
                @if($categoriaEvento->cupo_limite)
                    {{ $categoriaEvento->inscritos }} / {{ $categoriaEvento->cupo_limite }}
                @else
                    Ilimitado
                @endif
            </span>
        </div>
        
        <div class="flex items-start gap-2">
            <span class="font-semibold text-primary-600">Inscripciones:</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                @if($categoriaEvento->inscripciones_abiertas) bg-green-100 text-green-800
                @else bg-red-100 text-red-800 @endif">
                {{ $categoriaEvento->inscripciones_abiertas ? 'Abiertas' : 'Cerradas' }}
            </span>
        </div>
        
        @if($categoriaEvento->requisitos)
            <div>
                <p class="font-semibold text-primary-600">Requisitos:</p>
                <p class="text-gray-800 dark:text-white">{{ $categoriaEvento->requisitos }}</p>
            </div>
        @endif
        
        @if($categoriaEvento->reglas_especificas)
            <div>
                <p class="font-semibold text-primary-600">Reglas Específicas:</p>
                <p class="text-gray-800 dark:text-white">{{ $categoriaEvento->reglas_especificas }}</p>
            </div>
        @endif
    </div>
</div> 