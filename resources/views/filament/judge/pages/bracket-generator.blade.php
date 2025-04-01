<x-filament-panels::page>
    <form wire:submit="generarBracket">
        {{ $this->form }}

        <div class="mt-6 flex justify-end gap-3">
            <x-filament::button 
                href="/judge/llaves-competencia"
                color="gray" 
                outlined
            >
                Cancelar
            </x-filament::button>
            
            <x-filament::button 
                type="submit"
                color="primary"
            >
                Generar Bracket
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page> 