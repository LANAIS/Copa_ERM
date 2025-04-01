<x-filament-panels::page>
    <x-filament::section>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Vista previa de la Cuenta Atrás</h2>
            <x-filament::button 
                color="primary"
                icon="heroicon-o-calendar"
                wire:click="mountAction('updateDate')"
            >
                Editar Fecha
            </x-filament::button>
        </div>
        <p class="mb-4">Esta es la vista previa de cómo se mostrará la cuenta atrás en la página principal.</p>
        
        <div class="p-6 bg-gray-50 rounded-lg border">
            <div class="countdown preview" data-target-date="{{ $targetDate }}">
                <div class="grid grid-cols-4 gap-4">
                    <div class="flex flex-col items-center p-4 bg-white rounded-lg shadow">
                        <span class="countdown-number days text-4xl font-bold text-primary-600">00</span>
                        <span class="countdown-label text-sm font-medium text-gray-600 mt-2">Días</span>
                    </div>
                    <div class="flex flex-col items-center p-4 bg-white rounded-lg shadow">
                        <span class="countdown-number hours text-4xl font-bold text-primary-600">00</span>
                        <span class="countdown-label text-sm font-medium text-gray-600 mt-2">Horas</span>
                    </div>
                    <div class="flex flex-col items-center p-4 bg-white rounded-lg shadow">
                        <span class="countdown-number minutes text-4xl font-bold text-primary-600">00</span>
                        <span class="countdown-label text-sm font-medium text-gray-600 mt-2">Minutos</span>
                    </div>
                    <div class="flex flex-col items-center p-4 bg-white rounded-lg shadow">
                        <span class="countdown-number seconds text-4xl font-bold text-primary-600">00</span>
                        <span class="countdown-label text-sm font-medium text-gray-600 mt-2">Segundos</span>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 p-4 bg-white rounded-lg border flex justify-between items-center">
                <div class="text-sm">
                    <p class="font-semibold text-gray-700">Fecha objetivo actual:</p>
                    <p class="text-lg font-medium text-primary-600">{{ \Carbon\Carbon::parse($targetDate)->format('d/m/Y H:i') }}</p>
                </div>
                <x-filament::button 
                    size="sm"
                    icon="heroicon-o-pencil"
                    wire:click="mountAction('updateDate')"
                >
                    Cambiar
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
    
    <x-filament::section class="mt-6">
        <h2 class="text-xl font-bold mb-4">Información</h2>
        <div class="prose max-w-none">
            <p>Este componente permite configurar la fecha objetivo para la cuenta atrás que se muestra en la página principal del sitio.</p>
            <p>Cuando la fecha objetivo se alcance, el componente mostrará un mensaje indicando que el evento ha comenzado.</p>
        </div>
    </x-filament::section>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countdown = document.querySelector('.countdown.preview');
            if (!countdown) return;
            
            let targetDate = new Date(countdown.dataset.targetDate).getTime();
            let countdownInterval;

            function updateCountdown() {
                const now = new Date().getTime();
                const distance = targetDate - now;

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                countdown.querySelector('.days').textContent = String(days).padStart(2, '0');
                countdown.querySelector('.hours').textContent = String(hours).padStart(2, '0');
                countdown.querySelector('.minutes').textContent = String(minutes).padStart(2, '0');
                countdown.querySelector('.seconds').textContent = String(seconds).padStart(2, '0');

                if (distance < 0) {
                    clearInterval(countdownInterval);
                    countdown.innerHTML = '<div class="text-center p-4"><span class="text-xl font-bold text-primary-600">¡El evento ha comenzado!</span></div>';
                }
            }

            updateCountdown();
            countdownInterval = setInterval(updateCountdown, 1000);
            
            // Actualizar cuando cambie la fecha
            document.addEventListener('livewire:initialized', function () {
                Livewire.on('dateUpdated', function () {
                    setTimeout(function() {
                        location.reload();
                    }, 300);
                });
            });
        });
    </script>
    @endpush
</x-filament-panels::page>
