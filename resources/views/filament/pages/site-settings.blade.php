<x-filament-panels::page>
    <x-filament::section>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Logo del Sitio</h2>
            <x-filament::button 
                color="primary"
                icon="heroicon-o-photo"
                wire:click="mountAction('updateLogo')"
            >
                Cambiar Logo
            </x-filament::button>
        </div>
        <p class="mb-4">Este logo se mostrará en la cabecera del sitio y en diversos lugares de la aplicación.</p>
        
        <div class="p-6 bg-gray-50 rounded-lg border">
            <div class="flex flex-col items-center">
                <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-sm border mb-4 flex justify-center">
                    <img 
                        src="{{ $logoPath }}" 
                        alt="Logo del sitio" 
                        class="h-20 object-contain"
                    >
                </div>
                
                <div class="w-full max-w-md mt-2 p-4 bg-white rounded-lg border flex justify-between items-center">
                    <div class="text-sm">
                        <p class="font-semibold text-gray-700">Logo actual</p>
                        <p class="text-sm text-gray-500">Se recomienda un tamaño de 200x50 píxeles</p>
                    </div>
                    <x-filament::button 
                        size="sm"
                        icon="heroicon-o-pencil"
                        wire:click="mountAction('updateLogo')"
                    >
                        Cambiar
                    </x-filament::button>
                </div>
            </div>
        </div>
    </x-filament::section>
    
    <x-filament::section class="mt-6">
        <h2 class="text-xl font-bold mb-4">Información</h2>
        <div class="prose max-w-none">
            <p>El logo se mostrará en los siguientes lugares:</p>
            <ul>
                <li>Cabecera del sitio web principal</li>
                <li>Panel de administración</li>
                <li>Panel de competidores</li>
                <li>Favicon del sitio web</li>
            </ul>
            <p>Formatos aceptados: JPG, PNG, GIF, SVG. Tamaño máximo: 2MB.</p>
        </div>
    </x-filament::section>
    
    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function () {
            Livewire.on('logoUpdated', function () {
                setTimeout(function() {
                    location.reload();
                }, 300);
            });
        });
    </script>
    @endpush
</x-filament-panels::page> 